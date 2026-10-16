<?php

namespace App\Tests\Controller;

use App\Entity\Adresse;
use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\Couleur;
use App\Entity\Declinaison;
use App\Entity\Produit;
use App\Entity\User;
use App\Tests\ApiTestCase;

class CommandeControllerTest extends ApiTestCase
{
    /** Crée en base une catégorie, un produit, une couleur et une déclinaison avec le stock et le prix voulus */
    private function creerDeclinaison(int $stock, string $prix): Declinaison
    {
        $categorie = new Categorie();
        $categorie->setNomCategorie('Test');
        $this->em->persist($categorie);

        $produit = new Produit();
        $produit->setNomDuProduit('Produit test');
        $produit->setDescription('Description test');
        $produit->setCreationProduit(new \DateTimeImmutable());
        $produit->setCategorie($categorie);
        $this->em->persist($produit);

        $couleur = new Couleur();
        $couleur->setChoixCouleur('Test');
        $this->em->persist($couleur);

        $declinaison = new Declinaison();
        $declinaison->setProduit($produit);
        $declinaison->setCouleur($couleur);
        $declinaison->setPrix($prix);
        $declinaison->setStock($stock);
        $this->em->persist($declinaison);

        $this->em->flush();
        return $declinaison;
    }

    /** Crée une adresse de livraison appartenant à l'utilisateur donné */
    private function creerAdresse(User $proprietaire): Adresse
    {
        $adresse = new Adresse();
        $adresse->setType('livraison');
        $adresse->setRue('1 rue du Test');
        $adresse->setVille('Brive-la-Gaillarde');
        $adresse->setCodePostal('19100');
        $adresse->setPays('France');
        $adresse->setUtilisateur($proprietaire);
        $this->em->persist($adresse);
        $this->em->flush();
        return $adresse;
    }

    /** Lit le stock directement en base (sans passer par le cache de Doctrine) */
    private function stockEnBase(int $idDeclinaison): int
    {
        return (int) $this->em->getConnection()->fetchOne(
            'SELECT stock FROM DECLINAISON WHERE id_declinaison = :id',
            ['id' => $idDeclinaison]
        );
    }

    public function testCommandeNominaleRenvoie201EtDecrementeLeStock(): void
    {
        $this->connecter();
        $declinaison = $this->creerDeclinaison(stock: 5, prix: '14.90');
        $adresse = $this->creerAdresse($this->user);
        $idDeclinaison = $declinaison->getId();
        $idAdresse = $adresse->getId();

        $this->json('POST', '/api/panier/lignes', ['id_declinaison' => $idDeclinaison, 'quantite' => 2]);
        $commande = $this->json('POST', '/api/commandes', ['id_adresse_livraison' => $idAdresse]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame('29.80', $commande['total_ht']);

        // 5 en stock - 2 achetés = 3
        $this->assertSame(3, $this->stockEnBase($idDeclinaison));

        // Le panier est vidé après la commande
        $panier = $this->json('GET', '/api/panier');
        $this->assertSame([], $panier['lignes']);
    }

    public function testStockInsuffisantRenvoie409EtNeModifieRien(): void
    {
        $this->connecter();
        $declinaison = $this->creerDeclinaison(stock: 2, prix: '14.90');
        $adresse = $this->creerAdresse($this->user);
        $idDeclinaison = $declinaison->getId();
        $idAdresse = $adresse->getId();
        $idUser = $this->user->getId();

        $this->json('POST', '/api/panier/lignes', ['id_declinaison' => $idDeclinaison, 'quantite' => 2]);

        // Un autre client achète les 2 pièces entre-temps : on vide le stock directement en base.
        // (Un setStock(0) + flush() ne marcherait pas : entre deux requêtes, le client de test
        //  réinitialise l'EntityManager, l'objet $declinaison n'est plus suivi par Doctrine.)
        $this->em->getConnection()->executeStatement(
            'UPDATE DECLINAISON SET stock = 0 WHERE id_declinaison = :id',
            ['id' => $idDeclinaison]
        );

        // Précondition : la situation de départ est bien en place avant de tester le comportement
        $this->assertSame(0, $this->stockEnBase($idDeclinaison));

        $this->json('POST', '/api/commandes', ['id_adresse_livraison' => $idAdresse]);
        $this->assertResponseStatusCodeSame(409);

        // Rien n'a bougé : le stock n'est pas négatif et aucune commande n'a été créée
        $this->assertSame(0, $this->stockEnBase($idDeclinaison));
        $this->assertSame(0, (int) $this->em->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM COMMANDE WHERE id_utilisateur = :id',
            ['id' => $idUser]
        ));
    }
        public function testPanierVideRenvoie400(): void
{
    $this->connecter();
    $adresse = $this->creerAdresse($this->user);

    // Aucun article ajouté au panier
    $this->json('POST', '/api/commandes', ['id_adresse_livraison' => $adresse->getId()]);

    $this->assertResponseStatusCodeSame(400);
}
public function testAdresseDUnAutreClientRenvoie404(): void
{
    $this->connecter();   // l'utilisateur connecté = l'attaquant
    $idUser = $this->user->getId();

    // Un second utilisateur, propriétaire de l'adresse visée (pas besoin de le connecter)
    $autre = new User();
    $autre->setEmail('autre_' . uniqid() . '@exemple.fr');
    $autre->setNom('Autre');
    $autre->setPrenom('Client');
    $autre->setPassword('motDePasse');   // une chaîne quelconque suffit : cet utilisateur ne se connecte jamais
    $this->em->persist($autre);
    $this->em->flush();
    $adresseDeLAutre = $this->creerAdresse($autre);

    // L'attaquant a un vrai panier, pour que ce soit bien l'adresse qui bloque et pas le panier vide
    $declinaison = $this->creerDeclinaison(stock: 5, prix: '14.90');
    $this->json('POST', '/api/panier/lignes', ['id_declinaison' => $declinaison->getId(), 'quantite' => 1]);

    $this->json('POST', '/api/commandes', ['id_adresse_livraison' => $adresseDeLAutre->getId()]);

    $this->assertResponseStatusCodeSame(404);
    $this->assertSame(0, (int) $this->em->getConnection()->fetchOne(
        'SELECT COUNT(*) FROM COMMANDE WHERE id_utilisateur = :id',
        ['id' => $idUser]
    ));
}
}