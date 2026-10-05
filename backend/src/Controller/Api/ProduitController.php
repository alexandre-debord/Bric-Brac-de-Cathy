<?php

namespace App\Controller\Api;


use App\Entity\Image;
use App\Entity\Produit;
use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;


class ProduitController
{

    #[Route('/api/produits', methods: ['POST'])]
    public function produit(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {

            return new JsonResponse(['error' => 'Corps de requête JSON invalide.'], 400);
        }
        $categorie = $em->getRepository(Categorie::class)->find($data['categorie']);

        if ($categorie === null) {
            return new JsonResponse(['error' => 'introuvable'], 404);
        }

        $produit = new Produit();
        $produit->setCreationProduit(new \DateTimeImmutable());
        $produit->setDescription($data['description']);
        $produit->setNomDuProduit($data['nomDuProduit']);
        $produit->setCategorie($categorie);

        $em->persist($produit);
        $em->flush();


        return new JsonResponse([
            'ok' => true,
            'donnee' => $produit->getId()
        ]);
    }

    #[Route('/api/produits/{id}', methods: ['PUT'])]
    public function updateProduit(int $id, Request $request, EntityManagerInterface $em): JsonResponse {

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {

            return new JsonResponse(['error' => 'Corps de requête JSON invalide.'], 400);
        }
        $produit = $em->getRepository(Produit::class)->find($id);

        if ($produit === null) {
            return new JsonResponse(['error' => 'introuvable'], 404);
        }

                $categorie = $em->getRepository(Categorie::class)->find($data['categorie']);

        if ($categorie === null) {
            return new JsonResponse(['error' => 'introuvable'], 404);
        }

        $produit->setNomDuProduit($data['nomDuProduit']);
        $produit->setDescription($data['description']);
        $produit->setCategorie($categorie);

        $em->flush();

        return new JsonResponse([
            'ok' => true,
            'donnee' => $produit->getId()
        ]);
    }

        #[Route('/api/produits/{id}', methods: ['DELETE'])]
    public function deleteProduit(int $id, EntityManagerInterface $em): JsonResponse {

        $produit = $em->getRepository(Produit::class)->find($id);

        if ($produit === null) {
            return new JsonResponse(['error' => 'introuvable'], 404);
        }
        $em->remove($produit);

        $em->flush();

        return new JsonResponse([
            'ok' => true,
            'donnee' => $id
        ]);
    }
}
