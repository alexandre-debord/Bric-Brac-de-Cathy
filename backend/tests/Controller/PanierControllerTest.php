<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PanierControllerTest extends WebTestCase
{
    public function testPanierSansTokenRenvoie401(): void
    {
        $client = static::createClient();          // un navigateur simulé, sans serveur
        $client->request('GET', '/api/panier');            // la route du panier

        $this->assertResponseStatusCodeSame(401);  // le code attendu sans token
    }

    private function creerClientConnecte(): KernelBrowser
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $email = 'test_' . uniqid() . '@exemple.fr';
        $motDePasse = 'MotDePasse123!';

        $user = new User();
        $user->setEmail($email);
        $user->setNom('Test');
        $user->setPrenom('Test');
        $user->setPassword($hasher->hashPassword($user, $motDePasse));   // ton setter du mot de passe haché
        $em->persist($user);
        $em->flush();

        $client->request(
            'POST',
            '/api/login_check',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => $email, 'password' => $motDePasse])
        );
        $token = json_decode($client->getResponse()->getContent(), true)['token'];   // la clé du token dans la réponse

        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $token);
        return $client;
    }

    public function testPanierConnecteVideRenvoie200(): void
    {
        $client = $this->creerClientConnecte();
        $client->request('GET', '/api/panier');

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame([], $data['lignes']);   // un nouvel utilisateur a un panier vide
    }

}

