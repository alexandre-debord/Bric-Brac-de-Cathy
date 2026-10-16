<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;
    protected User $user;

    /** Crée un utilisateur de test, le connecte, et garde le client, l'EntityManager et l'utilisateur */
    protected function connecter(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();   // garde le même noyau (et la même base) entre les requêtes du test
        $container = static::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $email = 'test_' . uniqid() . '@exemple.fr';
        $motDePasse = 'MotDePasse123!';

        $this->user = new User();
        $this->user->setEmail($email);
        $this->user->setNom('Test');
        $this->user->setPrenom('Test');
        $this->user->setPassword($hasher->hashPassword($this->user, $motDePasse));
        $this->em->persist($this->user);
        $this->em->flush();

        $reponse = $this->json('POST', '/api/login_check', ['email' => $email, 'password' => $motDePasse]);
        $this->client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $reponse['token']);
    }

    /** Envoie une requête JSON et renvoie la réponse décodée */
    protected function json(string $methode, string $url, ?array $corps = null): ?array
    {
        $this->client->request($methode, $url,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $corps !== null ? json_encode($corps) : null
        );
        return json_decode($this->client->getResponse()->getContent(), true);
    }
}