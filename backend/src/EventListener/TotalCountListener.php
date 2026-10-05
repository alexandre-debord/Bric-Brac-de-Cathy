<?php

namespace App\EventListener;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TotalCountListener implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    // Contrairement à #[AsEventListener], cette méthode statique fait partie
    // du contrat de l'interface : Symfony l'appelle lui-même pour savoir
    // à quels événements s'abonner, sans passer par un attribut à part.
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->getMethod() !== 'GET' || $request->getPathInfo() !== '/api/produits') {
            return;
        }

        $total = $this->entityManager->getRepository(Produit::class)->count([]);

        $event->getResponse()->headers->set('X-Total-Count', (string) $total);
    }
}