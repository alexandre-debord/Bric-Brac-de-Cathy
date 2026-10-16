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
use App\Service\CalculateurTotal;

class CommandeController
{
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
