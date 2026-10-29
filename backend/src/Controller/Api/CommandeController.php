<?php

namespace App\Controller\Api;

use App\Entity\Commande;
use App\Entity\LigneDeCommande;
use App\Entity\Adresse;
use App\Entity\Panier;
use App\Entity\PanierLigne;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Service\CalculateurTotal;

class CommandeController
{
    // En haut de la classe, juste après « class CommandeController { »
    // Pour chaque statut : la liste des statuts vers lesquels il peut passer
    private const TRANSITIONS = [
        'en_attente_paiement' => ['payee', 'annulee'],
        'payee' => ['expediee', 'annulee'],
        'expediee' => [],
        'annulee' => [],
    ];

    #[Route('/api/commandes/{id}/statut', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function changerStatut(int $id, Request $request, EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker): JsonResponse
    {
        // 1. Réservé à l'admin : sinon 403 « Accès réservé à l'administrateur. »
        if (!$authChecker->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Accès réservé à l\'administrateur.'], 403);
        }


        // 2. Lire le corps JSON et récupérer le nouveau statut
        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || !isset($data['statut']) || !is_string($data['statut'])) {
            return new JsonResponse(['error' => 'Données invalides ou statut absent.'], 400);
        }
        $nouveauStatut = $data['statut'];

        // 3. Le statut demandé existe-t-il ?
        if (!array_key_exists($nouveauStatut, self::TRANSITIONS)) {
            $statutsAcceptes = implode(', ', array_keys(self::TRANSITIONS));
            return new JsonResponse([
                'error' => sprintf('Statut inconnu. Les statuts acceptés sont : %s.', $statutsAcceptes)
            ], 400);
        }

        // 4. Chercher la commande par son id
        $commande = $em->getRepository(Commande::class)->find($id);
        if ($commande === null) {
            return new JsonResponse(['error' => 'Commande introuvable.'], 404);
        }

        // 5. La transition est-elle autorisée depuis le statut ACTUEL ?
        $statutActuel = $commande->getStatutCommande();
        $transitionsPermises = self::TRANSITIONS[$statutActuel] ?? [];
        if (!in_array($nouveauStatut, $transitionsPermises, true)) {
            return new JsonResponse([
                'error' => sprintf('Impossible de passer de %s à %s.', $statutActuel, $nouveauStatut)
            ], 409);
        }


        // 6. Ouvrir une transaction (comme dans creer)
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            // 6a. Si le nouveau statut est « annulee » :
            //     pour chaque ligne de la commande, REMETTRE sa quantité dans le stock de sa déclinaison
            if ($nouveauStatut === 'annulee') {
                foreach ($commande->getLignesCommande() as $ligne) {
                    $declinaison = $ligne->getDeclinaison();
                    $quantite = $ligne->getQuantite();
                    $idDeclinaison = $declinaison->getId();

                    $conn->executeStatement(
                        'UPDATE DECLINAISON SET stock = stock + :q 
                         WHERE id_declinaison = :id',
                        [
                            'q'  => $quantite,
                            'id' => $idDeclinaison,
                        ]
                    );
                }
            }

            // 6b. Changer le statut de la commande, flush, commit
            $commande->setStatutCommande($nouveauStatut);
            $em->flush();
            $conn->commit();

            return new JsonResponse([
                'id_commande' => $commande->getId(),
                'statut'      => $commande->getStatutCommande(),
            ], 200);
        } catch (\Throwable $e) {
            if ($conn->isTransactionActive()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    #[Route('/api/commandes/{id}', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function detail(int $id, #[CurrentUser] User $user, EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker, CalculateurTotal $calculateur): JsonResponse
    {

        $criteres = ['id' => $id];


        if (!$authChecker->isGranted('ROLE_ADMIN')) {
            $criteres['utilisateur'] = $user;
        }

        $commande = $em->getRepository(Commande::class)->findOneBy($criteres);

        if ($commande === null) {
            return new JsonResponse(['error' => 'Commande introuvable.'], 404);
        }

        $lignes = [];
        foreach ($commande->getLignesCommande() as $ligne) {
            $declinaison = $ligne->getDeclinaison();
            $quantite = $ligne->getQuantite();
            $prix = $ligne->getPrixUnit();
            $sousTotalCentimes = $calculateur->enCentimes($prix) * $quantite;
            $lignes[] = [
                'produit'  => $declinaison->getProduit()->getNomDuProduit(),
                'couleur'  => $declinaison->getCouleur()->getChoixCouleur(),
                'quantite'  => $quantite,
                'prix_unit'  => $prix,
                'sous_total'  => $calculateur->formater($sousTotalCentimes),
            ];
        }

        $adresse = $commande->getAdresseLivraison();

        return new JsonResponse([
            'id'             => $commande->getId(),
            'date'           => $commande->getDateCommande()->format(\DateTimeInterface::ATOM),
            'statut'         => $commande->getStatutCommande(),
            'total_ht'       => $commande->getTotalHt(),
            'lignes'         => $lignes,
            'adresse_livraison' => [
                'rue'         => $adresse->getRue(),
                'code_postal' => $adresse->getCodePostal(),
                'ville'       => $adresse->getVille(),
                'pays'        => $adresse->getPays(),
            ]
        ], 200);
    }
    #[Route('/api/commandes', methods: ['GET'])]
    public function lister(#[CurrentUser] ?User $user, EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker): JsonResponse
    {

        if (!$user) {
            return new JsonResponse(['error' => 'Accès refusé. Vous devez être connecté.'], 401);
        }

        $qb = $em->getRepository(Commande::class)->createQueryBuilder('c');


        if (!$authChecker->isGranted('ROLE_ADMIN')) {
            $qb->andWhere('c.utilisateur = :user')
                ->setParameter('user', $user);
        }


        $qb->orderBy('c.dateCommande', 'DESC');

        $commandes = $qb->getQuery()->getResult();
        $data = [];


        foreach ($commandes as $commande) {
            $nombreArticles = 0;
            foreach ($commande->getLignesCommande() as $ligne) {
                $nombreArticles += $ligne->getQuantite();
            }

            $data[] = [
                'id'              => $commande->getId(),
                'date'            => $commande->getDateCommande()->format(\DateTimeInterface::ATOM),
                'statut'          => $commande->getStatutCommande(),
                'total_ht'           => $commande->getTotalHt(),
                'nombre_articles' => $nombreArticles,
            ];
        }

        return new JsonResponse($data, 200);
    }

    #[Route('/api/commandes', methods: ['POST'])]
    public function creer(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em, CalculateurTotal $calculateur): JsonResponse
    {
        // 1. Validation : livraison obligatoire (entier), facturation facultative (entier si présente)
        $data = json_decode($request->getContent(), true);
        if (
            !is_array($data)
            || !isset($data['id_adresse_livraison'])
            ||    !is_int($data['id_adresse_livraison'])
        ) {
            return new JsonResponse(['error' => 'Données invalides.'], 400);
        }
        if (isset($data['id_adresse_facturation']) && !is_int($data['id_adresse_facturation'])) {
            return new JsonResponse(['error' => 'Données invalides.'], 400);
        }

        // 2. L'adresse de livraison DE CET UTILISATEUR (sinon 404)
        $adresseLivraison = $em->getRepository(Adresse::class)->findOneBy([
            'id'          => $data['id_adresse_livraison'],
            'utilisateur' => $user,
        ]);
        if ($adresseLivraison === null) {
            return new JsonResponse(['error' => 'Adresse introuvable.'], 404);
        }

        // 3. L'adresse de facturation, si elle est fournie (même règle)
        $adresseFacturation = null;
        if (isset($data['id_adresse_facturation'])) {
            $adresseFacturation = $em->getRepository(Adresse::class)->findOneBy([
                'id'          => $data['id_adresse_facturation'],
                'utilisateur' => $user,
            ]);
            if ($adresseFacturation === null) {
                return new JsonResponse(['error' => 'Adresse introuvable.'], 404);
            }
        }

        // 4. Le panier doit contenir au moins une ligne
        $panier = $em->getRepository(Panier::class)->findOneBy(['utilisateur' => $user]);
        $lignes = $panier ? $em->getRepository(PanierLigne::class)->findBy(['panier' => $panier]) : [];
        if (count($lignes) === 0) {
            return new JsonResponse(['error' => 'Panier vide.'], 400);
        }

        $lignesPourTotal = [];
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            // Mise à jour des stocks et calcul du total HT en centimes
            foreach ($lignes as $ligne) {
                $declinaison = $ligne->getDeclinaison();
                $quantite = $ligne->getQuantite();
                $idDeclinaison = $declinaison->getId();

                $nbModifiees = $conn->executeStatement(
                    'UPDATE DECLINAISON SET stock = stock - :q1
                     WHERE id_declinaison = :id AND stock >= :q2',
                    [
                        'q1' => $quantite,
                        'q2' => $quantite,
                        'id' => $idDeclinaison,
                    ]
                );

                if ($nbModifiees === 0) {
                    $conn->rollBack();
                    return new JsonResponse(
                        ['error' => 'Stock insuffisant pour ' . $declinaison->getProduit()->getNomDuProduit()],
                        409
                    );
                }

                // Incrémentation du total HT en centimes
                $lignesPourTotal[] = ['prix' => $declinaison->getPrix(), 'quantite' => $quantite];
            }

            $totalCentimes = $calculateur->total($lignesPourTotal);

            // Création de l'entité Commande
            $commande = new Commande();
            $commande->setUtilisateur($user);
            $commande->setAdresseLivraison($adresseLivraison);
            $commande->setAdresseFacturation($adresseFacturation);
            $commande->setStatutCommande('en_attente_paiement');
            $commande->setTauxTva('0.00');
            $commande->setTotalHt($calculateur->formater($totalCentimes));
            $commande->setDateCommande(new \DateTimeImmutable());
            $em->persist($commande);

            // Transfert des lignes du panier vers la commande et vidage du panier
            foreach ($lignes as $ligne) {
                $ligneCommande = new LigneDeCommande();
                $ligneCommande->setCommande($commande);
                $ligneCommande->setDeclinaison($ligne->getDeclinaison());
                $ligneCommande->setQuantite($ligne->getQuantite());
                $ligneCommande->setPrixUnit((string) $ligne->getDeclinaison()->getPrix());
                $em->persist($ligneCommande);

                $em->remove($ligne);
            }

            $em->flush();
            $conn->commit();

            return new JsonResponse([
                'id_commande' => $commande->getId(),
                'total_ht'    => $commande->getTotalHt(),
                'statut'      => $commande->getStatutCommande(),
            ], 201);
        } catch (\Throwable $e) {
            if ($conn->isTransactionActive()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }
}
