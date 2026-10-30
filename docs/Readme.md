# Le Bric à Brac de Cathy

Boutique en ligne pour une micro-entreprise artisanale de Brive-la-Gaillarde, qui fabrique des bijoux en diamond painting. Le site permet à l'artisane de vendre ses créations dans toute la France, au-delà des marchés locaux.

Projet réalisé de A à Z en 2026, pendant ma formation **Développeur Web et Web Mobile** (titre professionnel, AFPA de Brive) : maquettes, base de données, API, front-end, tests et sécurité.

## Stack technique

| Partie | Technologies |
|---|---|
| Conception | Figma (maquettes ordinateur et mobile), Looping (MCD / MLD) |
| Front-end | React 19, Vite, React Bootstrap, React Router |
| Back-end | PHP 8.5, Symfony 8.1, Doctrine ORM, API Platform, LexikJWT |
| Base de données | MySQL 8.4 (InnoDB) |
| Images | Cloudinary |
| Tests | PHPUnit (tests unitaires et fonctionnels), Bruno (tests manuels de l'API) |

## Fonctionnalités

**Réalisées**

- Catalogue filtrable par catégorie, fiche produit avec choix de la couleur, prix et stock par déclinaison
- Panier pour les visiteurs (navigateur) et les clients connectés (base de données), fusionnés à la connexion
- Inscription, connexion par jeton JWT, déconnexion automatique à l'expiration de la session
- Passage de commande avec adresse de livraison
- API d'administration : produits, catégories, déclinaisons, photos, consultation des commandes et changement de statut (avec remise en stock à l'annulation)
- Interface responsive, pensée d'abord pour le mobile

**En cours**

- Espace d'administration côté front (gestion des commandes et des produits)
- Paiement en ligne
- Pages de réinitialisation du mot de passe côté front

## Points techniques

- **Pas de survente possible** : le stock est vérifié et décrémenté en une seule requête SQL conditionnelle, à l'intérieur d'une transaction. Si une pièce manque, toute la commande est annulée.
- **Prix figé** : chaque ligne de commande enregistre le prix au moment de l'achat ; l'historique reste exact si les prix changent.
- **Calcul en centimes** : les totaux sont calculés en entiers pour éviter les erreurs d'arrondi des nombres à virgule.
- **Un seul point d'entrée pour l'API côté front** (`apiFetch` / `useApi`) : gestion uniforme des erreurs et de l'expiration de session.

## Sécurité

- Mots de passe hachés (bcrypt), liste blanche des champs à l'inscription (un client ne peut pas se donner le rôle admin)
- Chaque client n'accède qu'à ses propres adresses et commandes (protection IDOR : réponse 404)
- Routes d'écriture réservées à l'administrateur, y compris celles générées par API Platform
- Jeton de réinitialisation du mot de passe aléatoire, à usage unique, stocké sous forme d'empreinte SHA-256
- Envoi d'images contrôlé (type réel du fichier, taille maximale)
- Dépendances auditées (`composer audit`, `npm audit`) et mises à jour

## Tests

```bash
cd backend
php bin/phpunit
```

Tests fonctionnels de l'API (création de commande, stock insuffisant, panier vide, accès aux données d'un autre client) et tests unitaires du calcul du total, sur une base de test séparée.

## Installation en local

**Prérequis** : PHP 8.5 (extension sodium), Composer, Node.js, MySQL 8.

```bash
# Back-end
cd backend
composer install
# Créer backend/.env.local avec DATABASE_URL, JWT_PASSPHRASE et CLOUDINARY_URL
php bin/console lexik:jwt:generate-keypair
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony server:start

# Front-end
cd ../frontend
npm install
npm run dev
```

## Structure

```
backend/   API Symfony (contrôleurs, entités, services, tests)
frontend/  Application React (pages, composants, hooks, utilitaires)
docs/      Modèle de données (Looping)
```

## Auteur

**Alexandre Debord** — développeur web en formation, à la recherche d'une alternance (titre Concepteur Développeur d'Applications).

- GitHub : [alexandre-debord](https://github.com/alexandre-debord)
- LinkedIn : [Alexandre Debord](https://www.linkedin.com/in/alexandre-debord-100b55353/)