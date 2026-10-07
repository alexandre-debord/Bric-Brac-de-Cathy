<?php

namespace App\Controller\Api;

use App\Entity\Adresse;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpFoundation\Request;

class AdresseController
{
    #[Route('/api/adresses', methods: ['GET'])]
    public function lister(#[CurrentUser] User $user, EntityManagerInterface $em): JsonResponse
    {
        $adresses = $em->getRepository(Adresse::class)->findBy(['utilisateur' => $user]);

        return new JsonResponse(array_map(
            fn (Adresse $a) => $this->adresseEnTableau($a),
            $adresses
        ));
    }

    #[Route('/api/adresses', methods: ['POST'])]
    public function creer(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Données invalides.'], 400);
        }

        // longueur max de chaque champ = la "length" de la colonne dans Adresse.php
        $longueursMax = [
            'type'        => 50,
            'rue'         => 255,
            'ville'       => 50,
            'code_postal' => 5,
            'pays'        => 50,
        ];

        foreach ($longueursMax as $champ => $max) {
            if (
                !isset($data[$champ])
                || !is_string($data[$champ])
                || trim($data[$champ]) === ''
                || mb_strlen(trim($data[$champ])) > $max
            ) {
                return new JsonResponse(['error' => "Champ invalide : $champ"], 400);
            }
        }

if (!in_array($data['type'], ['livraison', 'facturation'], true)) {
    return new JsonResponse(['error' => 'Champ invalide : type'], 400);
}

if (!preg_match('/^\d{5}$/', $data['code_postal'])) {
    return new JsonResponse(['error' => 'Champ invalide : code_postal'], 400);
}

$adresse = new Adresse();
        $adresse->setType(trim($data['type']));
        $adresse->setRue(trim($data['rue']));
        $adresse->setCodePostal(trim($data['code_postal']));
        $adresse->setVille(trim($data['ville']));
        $adresse->setPays(trim($data['pays']));
        $adresse->setUtilisateur($user);

        $em->persist($adresse);
        $em->flush();

        return new JsonResponse($this->adresseEnTableau($adresse), 201);
    }

    private function adresseEnTableau(Adresse $a): array
    {
        return [
            'id'          => $a->getId(),
            'type'        => $a->getType(),
            'rue'         => $a->getRue(),
            'ville'       => $a->getVille(),
            'code_postal' => $a->getCodePostal(),
            'pays'        => $a->getPays(),
        ];
    }
}