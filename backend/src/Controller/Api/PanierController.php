<?php

namespace App\Controller\Api;

use App\Entity\Panier;
use App\Entity\PanierLigne;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Declinaison;

class PanierController
{
    #[Route('/api/panier', methods: ['GET'])]
    public function lire(#[CurrentUser] User $user, EntityManagerInterface $em): JsonResponse
    {

        $panier = $em->getRepository(Panier::class)->findOneBy(['utilisateur' => $user]);

        return new JsonResponse($this->panierEnTableau($panier, $em));
    }

    // Mise en forme commune à toutes les routes du panier
    private function panierEnTableau(?Panier $panier, EntityManagerInterface $em): array
    {

        if ($panier === null) {
            return ['lignes' => [], 'total' => 0];
        }

        $lignes = $em->getRepository(PanierLigne::class)->findBy(['panier' => $panier]);

        $resultat = [];
        $total = 0;

        foreach ($lignes as $ligne) {
            $declinaison = $ligne->getDeclinaison();
            $produit = $declinaison->getProduit();
            $prix = (float) $declinaison->getPrix();
            $quantite = $ligne->getQuantite();
            $premiereImage = $produit->getImages()->first();
            $image = $premiereImage ? $premiereImage->getUrlImage() : '';

            $resultat[] = [
                'id_declinaison' => $declinaison->getId(),
                'nom'            => $produit->getNomDuProduit(),
                'couleur'        => $declinaison->getCouleur()?->getChoixCouleur() ?? '',
                'prix'           => $prix,
                'image'          => $image,
                'stock'          => $declinaison->getStock(),
                'quantite'       => $ligne->getQuantite(),
            ];

            $total += $prix * $quantite;
        }

        return ['lignes' => $resultat, 'total' => round($total, 2)];
    }

    #[Route('/api/panier/lignes', methods: ['POST'])]
    public function ajouter(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em): JsonResponse
    {
        // 1. Lecture et validation du JSON
        $data = json_decode($request->getContent(), true);
        if (
            !is_array($data)
            || !isset($data['id_declinaison'], $data['quantite'])
            || !is_int($data['quantite'])
            || !is_int($data['id_declinaison'])
            || $data['quantite'] < 1
        ) {
            return new JsonResponse(['error' => 'Données invalides.'], 400);
        }

        // 2. La déclinaison existe-t-elle ?
        $declinaison = $em->getRepository(Declinaison::class)->find($data['id_declinaison']);
        if ($declinaison === null) {
            return new JsonResponse(['error' => 'Déclinaison introuvable.'], 404);
        }

        // 3. Le panier de l'utilisateur, créé s'il n'existe pas encore
        $panier = $em->getRepository(Panier::class)->findOneBy(['utilisateur' => $user]);
        if ($panier === null) {
            $panier = new Panier();
            $panier->setUtilisateur($user);
            $em->persist($panier);
        }
        // 4. Une ligne existe-t-elle déjà pour cette déclinaison dans ce panier ?
        $ligne = $em->getRepository(PanierLigne::class)->findOneBy([
            'panier'      => $panier,
            'declinaison' => $declinaison,
        ]);
        $dejaDansPanier = $ligne ? $ligne->getQuantite() : 0;

        // 5. Nouvelle quantité plafonnée au stock
        $nouvelleQuantite = min($dejaDansPanier + $data['quantite'], $declinaison->getStock());
        if ($nouvelleQuantite <= $dejaDansPanier) {
            return new JsonResponse(['error' => 'Stock insuffisant.'], 409);
        }

        if ($ligne === null) {
            $ligne = new PanierLigne();
            $ligne->setPanier($panier);
            $ligne->setDeclinaison($declinaison);
            $em->persist($ligne);
        }
        $ligne->setQuantite($nouvelleQuantite);
        $panier->setModificationPanier(new \DateTimeImmutable());

        // 6. Enregistrement et réponse
        $em->flush();

        return new JsonResponse($this->panierEnTableau($panier, $em));
    }

    #[Route('/api/panier/lignes/{idDeclinaison}', methods: ['DELETE'], requirements: ['idDeclinaison' => '\d+'])]
    public function supprimerLigne(int $idDeclinaison, #[CurrentUser] User $user, EntityManagerInterface $em): JsonResponse
    {
        $panier = $em->getRepository(Panier::class)->findOneBy(['utilisateur' => $user]);
        if ($panier === null) {
            return new JsonResponse(['error' => 'Article absent du panier.'], 404);
        }

        // [A] La ligne de CE panier pour CETTE déclinaison
        $ligne = $em->getRepository(PanierLigne::class)->findOneBy([
            'panier'      => $panier,
            'declinaison' => $idDeclinaison,
        ]);
        if ($ligne === null) {
            return new JsonResponse(['error' => 'Article absent du panier.'], 404);
        }

        // [B] Supprime la ligne, puis enregistre
        $em->remove($ligne);
        $panier->setModificationPanier(new \DateTimeImmutable());
        $em->flush();

        return new JsonResponse($this->panierEnTableau($panier, $em));
    }

    #[Route('/api/panier', methods: ['DELETE'])]
    public function vider(#[CurrentUser] User $user, EntityManagerInterface $em): JsonResponse
    {
        $panier = $em->getRepository(Panier::class)->findOneBy(['utilisateur' => $user]);

        // [C] S'il y a un panier, on le supprime : ses lignes partent avec (ON DELETE CASCADE)
        if ($panier !== null) {
            $em->remove($panier);

            $em->flush();
        }

        // [D] Dans tous les cas, on renvoie un panier vide
        return new JsonResponse($this->panierEnTableau(null, $em));
    }

    #[Route('/api/panier/lignes/{idDeclinaison}', methods: ['PATCH'], requirements: ['idDeclinaison' => '\d+'])]
    public function changerQuantite(int $idDeclinaison, #[CurrentUser] User $user, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (
            !is_array($data)
            || !isset($data['quantite'])
            || !is_int($data['quantite'])
            || $data['quantite'] < 1
        ) {
            return new JsonResponse(['error' => 'Données invalides.'], 400);
        }

        $panier = $em->getRepository(Panier::class)->findOneBy(['utilisateur' => $user]);
        if ($panier === null) {
            return new JsonResponse(['error' => 'Article absent du panier.'], 404);
        }

        $ligne = $em->getRepository(PanierLigne::class)->findOneBy([
            'panier'      => $panier,
            'declinaison' => $idDeclinaison,
        ]);
        if ($ligne === null) {
            return new JsonResponse(['error' => 'Article absent du panier.'], 404);
        }

        $stock = $ligne->getDeclinaison()->getStock();
        $nouvelleQuantite = min($data['quantite'], $stock);

        $ligne->setQuantite($nouvelleQuantite);
        $panier->setModificationPanier(new \DateTimeImmutable());
        $em->flush();

        return new JsonResponse($this->panierEnTableau($panier, $em));
    }

    #[Route('/api/panier/fusion', methods: ['POST'])]
    public function fusionner(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || !isset($data['lignes']) || !is_array($data['lignes'])) {
            return new JsonResponse(['error' => 'Données invalides.'], 400);
        }

        $panier = $em->getRepository(Panier::class)->findOneBy(['utilisateur' => $user]);
        if ($panier === null) {
            $panier = new Panier();
            $panier->setUtilisateur($user);
            $em->persist($panier);
            $em->flush();
        }

        $lignesParDeclinaison = [];
        foreach ($em->getRepository(PanierLigne::class)->findBy(['panier' => $panier]) as $ligne) {
            $lignesParDeclinaison[$ligne->getDeclinaison()->getId()] = $ligne;
        }

        foreach ($data['lignes'] as $item) {
            if (
                !is_array($item)
                || !isset($item['id_declinaison'], $item['quantite'])
                || !is_int($item['id_declinaison'])
                || !is_int($item['quantite'])
                || $item['quantite'] < 1
            ) {
                continue;
            }
            $declinaison = $em->getRepository(Declinaison::class)->find($item['id_declinaison']);
            if ($declinaison === null) {
                continue; // produit supprimé depuis : on l'ignore
            }

            $ligne = $lignesParDeclinaison[$item['id_declinaison']] ?? null;
            $dejaDansPanier = $ligne ? $ligne->getQuantite() : 0;


            $nouvelleQuantite = min($dejaDansPanier + $item['quantite'], $declinaison->getStock());
            if ($nouvelleQuantite <= $dejaDansPanier) {
                continue; // stock déjà atteint : rien à ajouter
            }

            if ($ligne === null) {
                $ligne = new PanierLigne();
                $ligne->setPanier($panier);
                $ligne->setDeclinaison($declinaison);
                $em->persist($ligne);
                $lignesParDeclinaison[$item['id_declinaison']] = $ligne; 
            }
            $ligne->setQuantite($nouvelleQuantite);
        }

        $panier->setModificationPanier(new \DateTimeImmutable());
        $em->flush();

        return new JsonResponse($this->panierEnTableau($panier, $em));
    }
}
