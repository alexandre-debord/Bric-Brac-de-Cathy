<?php

namespace App\Controller\Api;

use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class CategorieController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    #[Route('/api/categories', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, (int) $request->query->get('limit', 10));
        $offset = ($page - 1) * $limit;

        $repository = $this->entityManager->getRepository(Categorie::class);

        $categories = $repository->findBy([], null, $limit, $offset);

        // Même critère vide que findBy() ci-dessus, mais sans limit/offset :
        // ça compte TOUTES les lignes de la table, pas seulement celles de la page.
        $total = $repository->count([]);

        $data = array_map(
            fn(Categorie $categorie) => [
                'id' => $categorie->getId(),
                'nomCategorie' => $categorie->getNomCategorie(),
                'description' => $categorie->getDescription(),
            ],
            $categories
        );

        return new JsonResponse([
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'data' => $data,
        ]);
    }

    #[Route('/api/categories', methods: ['POST'])]
    public function categorie(EntityManagerInterface $em, Request $request): JsonResponse
    {

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {

            return new JsonResponse(['error' => 'Corps de requête JSON invalide.'], 400);
        }

        $categorie = new Categorie();
        $categorie->setNomCategorie($data['nomCategorie']);
        $categorie->setDescription($data['description']);

        $em->persist($categorie);
        $em->flush();


        return new JsonResponse([
            'ok' => true,
            'donnee' => $categorie->getId()
        ]);
    }

    #[Route('/api/categories/{id}', methods: ['PUT'])]
        public function updateCategorie(int $id, Request $request, EntityManagerInterface $em): JsonResponse {

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {

            return new JsonResponse(['error' => 'Corps de requête JSON invalide.'], 400);
        }
        $categorie = $em->getRepository(Categorie::class)->find($id);

        if ($categorie === null) {
            return new JsonResponse(['error' => 'introuvable'], 404);
        }

                $categorie->setNomCategorie($data['nomCategorie']);
        $categorie->setDescription($data['description']);

        $em->flush();


        return new JsonResponse([
            'ok' => true,
            'donnee' => $id
        ]);
}

        #[Route('/api/categories/{id}', methods: ['DELETE'])]
    public function deleteCategorie(int $id, EntityManagerInterface $em): JsonResponse {

        $categorie = $em->getRepository(Categorie::class)->find($id);

        if ($categorie === null) {
            return new JsonResponse(['error' => 'introuvable'], 404);
        }
        $em->remove($categorie);

        $em->flush();

        return new JsonResponse([
            'ok' => true,
            'donnee' => $id
        ]);
    }
}