<?php

namespace App\Controller\Api;


use App\Entity\Image;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;


class ImageController
{

    #[Route('/api/produits/{id}/images', methods: ['POST'])]
    public function uploadImage(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $file = $request->files->get('image');


        if ($file === null) {
            return new JsonResponse(['error' => 'Message expliquant ce qui manque'], 400);
        }
        $produit = $em->getRepository(Produit::class)->find($id);

        if ($produit === null) {
            return new JsonResponse(['error' => 'introuvable'], 404);
        }



        $chemin = $file->getPathname();
        Configuration::instance($_ENV['CLOUDINARY_URL']);

        $upload = new UploadApi();
        $result = $upload->upload($chemin);

        $image = new Image();
        $image->setUrlImage($result['secure_url']);
        $image->setProduit($produit);

        $em->persist($image);
        $em->flush();
        
        $file = [
            'nomOriginal' => $file->getClientOriginalName(),
            'taille' => $file->getSize(),
            'type' => $file->getMimeType(),
        ];
        return new JsonResponse([
            'ok' => true,
            'donnee' => $file,
            'urlCloudinary' => $result['secure_url']
        ]);
    }
}
