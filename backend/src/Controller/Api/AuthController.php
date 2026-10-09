<?php

namespace App\Controller\Api;

// Les "use" importent des classes venant d'ailleurs.
// Sans eux, il faudrait écrire le nom complet à chaque fois :
// \Symfony\Component\HttpFoundation\Request au lieu de Request.
use App\Entity\ResetMdp;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;   // Attribute, PAS Annotation (Symfony 8)
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

// AbstractController fournit des raccourcis comme $this->json().
// Sans l'étendre, il faudrait construire les réponses HTTP à la main.
class AuthController extends AbstractController
{
    // ═══════════════════════════════════════════════════════════════
    //  INSCRIPTION
    // ═══════════════════════════════════════════════════════════════

    // L'attribut #[Route] déclare l'URL, le nom interne, et les méthodes HTTP acceptées.
    // Symfony scanne le dossier src/Controller/ au démarrage pour trouver ces attributs.
    // methods: ['POST'] signifie qu'un GET sur cette URL renverra 405 Method Not Allowed.
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]

    // Les paramètres typés sont fournis automatiquement par Symfony : c'est l'injection
    // de dépendances. Tu déclares ce dont tu as besoin, le conteneur te le donne.
    // En PHP classique tu ferais "new PDO(...)" ; ici tu ne construis rien toi-même,
    // ce qui rend le code testable et évite les variables globales.
    public function register(
        Request $request,                    // la requête HTTP reçue
        ValidatorInterface $validator,       // vérifie les contraintes #[Assert\...] de l'entité
        UserPasswordHasherInterface $hasher, // transforme un mot de passe en empreinte
        EntityManagerInterface $em,          // parle à la base via Doctrine
    ): JsonResponse {

        // ─── 1. Lire le corps JSON ───
        // getContent() renvoie le texte brut envoyé par le client.
        // json_decode(..., true) le transforme en tableau associatif PHP.
        $data = json_decode($request->getContent(), true);

        // json_decode renvoie null si le texte n'était pas du JSON valide.
        // Sans ce test, les lignes suivantes planteraient avec un message obscur.
        if (!is_array($data)) {
            return $this->json(['error' => 'Corps de requête JSON invalide.'], 400);
        }

        // ─── 2. Construire l'objet User ───
        // On lit UNIQUEMENT les champs autorisés. Un champ "role" envoyé par le client
        // est simplement ignoré : sinon n'importe qui deviendrait administrateur en
        // ajoutant "role":"admin" à sa requête, puisque cette route est publique.
        // La valeur par défaut 'user' de l'entité s'applique automatiquement.
        $user = new User();
        $user->setEmail($data['email'] ?? '');
        $user->setNom($data['nom'] ?? '');
        $user->setPrenom($data['prenom'] ?? '');
        $user->setPlainPassword($data['password'] ?? null);

        // ─── 3. Valider ───
        // validate() déclenche toutes les contraintes déclarées en attributs sur User :
        // NotBlank, Email, Length, et UniqueEntity qui fait un SELECT pour vérifier
        // que l'email n'est pas déjà pris. Une seule ligne remplace tous les if
        // de vérification ET la requête d'unicité.
        $erreurs = $validator->validate($user);

        if (count($erreurs) > 0) {
            $details = [];

            // Chaque violation expose le champ fautif et le message associé.
            // Les crochets [] accumulent : un champ peut avoir plusieurs erreurs.
            // Résultat : {"email": ["Format invalide."], "nom": ["Obligatoire."]}
            // Le front peut alors afficher chaque message sous le bon input.
            foreach ($erreurs as $erreur) {
                $details[$erreur->getPropertyPath()][] = $erreur->getMessage();
            }

            return $this->json(['errors' => $details], 400);
        }

        // ─── 4. Hasher le mot de passe ───
        // $user est passé en premier argument parce que la configuration
        // password_hashers de security.yaml est branchée sur son INTERFACE.
        // C'est donc le type de l'objet qui détermine l'algorithme utilisé.
        // Ne jamais appeler password_hash() directement : le service gère aussi
        // la migration automatique si tu changes d'algorithme plus tard.
        $user->setPassword($hasher->hashPassword($user, $user->getPlainPassword()));

        // Vide $plainPassword. Le mot de passe en clair ne doit pas rester
        // en mémoire plus longtemps que nécessaire.
        $user->eraseCredentials();

        // ─── 5. Enregistrer ───
        // persist() dit à Doctrine "cet objet est nouveau, prévois un INSERT".
        // flush() exécute réellement la requête SQL.
        // Les deux sont nécessaires pour un objet neuf.
        $em->persist($user);
        try {
            $em->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(['errors' => ['email' => ['Cet email est déjà utilisé.']]], 409);
        }
        // ─── 6. Répondre ───
        // getId() ne renvoie une valeur qu'APRÈS le flush : l'id vient de
        // l'auto-increment de MySQL, il n'existe pas avant l'INSERT.
        // On ne renvoie jamais le hash ni le mot de passe.
        // 201 = Created, le code HTTP correct pour une création réussie.
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
        ], 201);
    }

    // ═══════════════════════════════════════════════════════════════
    //  DEMANDE DE RÉINITIALISATION
    // ═══════════════════════════════════════════════════════════════

    #[Route('/api/forgot-password', name: 'api_forgot_password', methods: ['POST'])]
    public function forgotPassword(
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Corps de requête JSON invalide.'], 400);
        }

        $email = $data['email'] ?? null;

        // $token est initialisé AVANT le if. Sans ça, la variable pourrait ne
        // jamais exister au moment du return, ce qui provoquerait une notice PHP.
        $token = null;

        if ($email) {
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            // findOneBy renvoie null si aucune ligne ne correspond.
            // Ce test est indispensable : setUtilisateur() est typé User et
            // lèverait une TypeError fatale si on lui passait null — ce qui
            // produirait un 500 pour un email inconnu et un 200 pour un email
            // connu, révélant exactement ce qu'on cherche à cacher.
            if ($user) {

                // random_bytes s'adosse au générateur cryptographique du système.
                // JAMAIS uniqid() ou rand() ici : ils dérivent de l'horloge et
                // sont donc prédictibles par un attaquant.
                // bin2hex convertit les 32 octets en 64 caractères hexadécimaux.
                $token = bin2hex(random_bytes(32));

                $reset = new ResetMdp();

                // On stocke l'EMPREINTE du jeton, pas le jeton lui-même.
                // Si la base fuite, l'attaquant n'a que des hashs, inutilisables.
                // Même raisonnement que pour les mots de passe.
                // sha256 et pas bcrypt : un jeton de 256 bits aléatoires n'est pas
                // devinable par force brute, le hachage lent n'apporterait rien.
                $reset->setToken(hash('sha256', $token));

                // DateTimeImmutable plutôt que DateTime : une date d'expiration ne
                // doit jamais être modifiée après création. L'objet immuable garantit
                // qu'aucun appel à modify() ailleurs ne pourra la décaler.
                $reset->setExpiration(new \DateTimeImmutable('+1 hour'));
                $reset->setCreationMdp(new \DateTimeImmutable());
                $reset->setUtilisateur($user);

                $em->persist($reset);
                $em->flush();
            }
        }

        // Ce return est VOLONTAIREMENT hors des deux if.
        // La réponse est strictement identique que l'email existe ou non :
        // même message, même code HTTP 200. Sinon la route deviendrait un outil
        // d'énumération — un attaquant testerait une liste d'adresses et saurait
        // lesquelles sont inscrites sur le site.
        return $this->json([
            'message' => 'Si un compte existe avec cette adresse, un lien de réinitialisation a été envoyé.',
            // TEMPORAIRE — à retirer quand Mailer sera configuré.
            // En production ce jeton part par email, jamais dans la réponse HTTP.
            'token' => $this->getParameter('kernel.environment') === 'dev' ? $token : null,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  APPLICATION DU NOUVEAU MOT DE PASSE
    // ═══════════════════════════════════════════════════════════════

    #[Route('/api/reset-password', name: 'api_reset_password', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        ValidatorInterface $validator,
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Corps de requête JSON invalide.'], 400);
        }

        $tokenRecu = $data['token'] ?? null;
        $nouveauMdp = $data['password'] ?? null;

        // Le || signifie "ou" : la condition est vraie si l'un OU l'autre manque.
        if (!$tokenRecu || !$nouveauMdp) {
            return $this->json(['error' => 'Token et mot de passe requis.'], 400);
        }

        // ─── Retrouver la demande ───
        // Le client envoie le jeton en clair, la base contient son empreinte.
        // On recalcule donc l'empreinte pour chercher la ligne.
        //
        // sha256 est DÉTERMINISTE : la même entrée donne toujours la même sortie.
        // C'est ce qui permet de s'en servir comme clé de recherche.
        // Un hachage de mot de passe, lui, ajoute un sel aléatoire à chaque appel,
        // et ne pourrait donc pas servir ainsi.
        $tokenHache = hash('sha256', $tokenRecu);

        $reset = $em->getRepository(ResetMdp::class)->findOneBy([
            'token' => $tokenHache,
        ]);

        // ─── Vérifier la validité ───
        // Deux échecs possibles : le jeton n'existe pas (inventé, ou déjà utilisé),
        // ou il a expiré. PHP compare directement deux objets de date.
        //
        // UN SEUL message pour les deux cas : distinguer "jeton inconnu" de
        // "jeton expiré" renseignerait un attaquant sur ses tentatives.
        if (!$reset || $reset->getExpiration() < new \DateTimeImmutable()) {
            return $this->json(['error' => 'Lien invalide ou expiré.'], 400);
        }

        // Si on arrive ici, $reset existe et est valide.
        // getUtilisateur() suit la relation ManyToOne : Doctrine va chercher
        // la ligne correspondante dans UTILISATEUR et renvoie un objet User.
        $user = $reset->getUtilisateur();

        // ─── Valider le nouveau mot de passe ───
        // On repasse par le Validator plutôt que de réécrire la règle des
        // 8 caractères : elle vit dans l'entité et s'applique partout.
        // Si tu la changes un jour, inscription et réinitialisation suivent.
        $user->setPlainPassword($nouveauMdp);
        $erreurs = $validator->validate($user);

        if (count($erreurs) > 0) {
            $details = [];
            foreach ($erreurs as $erreur) {
                $details[$erreur->getPropertyPath()][] = $erreur->getMessage();
            }

            return $this->json(['errors' => $details], 400);
        }

        // ─── Appliquer ───
        $user->setPassword($hasher->hashPassword($user, $nouveauMdp));
        $user->eraseCredentials();

        // remove() marque la ligne pour suppression : c'est ce qui rend le lien
        // à USAGE UNIQUE. Sans cette ligne, quelqu'un qui retrouverait l'email
        // pourrait rechanger le mot de passe pendant toute l'heure de validité.
        $em->remove($reset);

        // Un seul flush pour les deux opérations : l'UPDATE sur UTILISATEUR et
        // le DELETE sur RESET_MDP partent dans la même transaction.
        //
        // Pas de persist() pour $user : Doctrine le suit déjà puisqu'il vient
        // d'être chargé depuis la base. Il compare son état avant/après et
        // génère l'UPDATE tout seul. persist() ne sert que pour un objet NEUF.
        $em->flush();

        return $this->json([
            'message' => 'Mot de passe modifié avec succès.',
        ]);
    }
}
