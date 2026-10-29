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

        // on regarde si le fichier est present 
        if ($file === null) {
            return new JsonResponse(['error' => 'Veuillez envoyer une image.'], 400);
        }

                // 1 bis. L'envoi s'est-il bien passé côté PHP ?
        if (!$file->isValid()) {
            return new JsonResponse(['error' => 'L’envoi de l’image a échoué.'], 400);
        }

        // type d'image autoriser
        $typesAutorises = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file->getMimeType(), $typesAutorises, true)) {
            return new JsonResponse(['error' => 'Formats acceptés : JPEG, PNG ou WebP.'], 400);
        }

        // limitation de la taille 
        if ($file->getSize() > 2 * 1024 * 1024) {
            return new JsonResponse(['error' => 'L\'image ne doit pas dépasser 2 Mo.'], 400);
        }

        // on cherche si le produit existe
        $produit = $em->getRepository(Produit::class)->find($id);
        if ($produit === null) {
            return new JsonResponse(['error' => 'Produit introuvable.'], 404);
        }

        $chemin = $file->getPathname();
        Configuration::instance($_ENV['CLOUDINARY_URL']);

        $upload = new UploadApi();
                try {
            $result = $upload->upload($chemin);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Envoi de l’image impossible, réessaie plus tard.'], 502);
        }

        $image = new Image();
        $image->setUrlImage($result['secure_url']);
        $image->setProduit($produit);

        $em->persist($image);
        $em->flush();

        $infos = [
            'nomOriginal' => $file->getClientOriginalName(),
            'taille' => $file->getSize(),
            'type' => $file->getMimeType(),
        ];
                return new JsonResponse([
            'ok' => true,
            'donnee' => $infos,
            'urlCloudinary' => $result['secure_url']
        ], 201);
    }
}
