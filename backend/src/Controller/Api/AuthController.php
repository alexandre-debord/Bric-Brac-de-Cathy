<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\ResetMdp;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
    ): JsonResponse {
        // 1. Lire le corps JSON.
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Corps de requête JSON invalide.'], 400);
        }

        // 2. Remplir l'objet. On ne lit QUE les champs autorisés :
        //    un champ "role" envoyé par le client est simplement ignoré.
        $user = new User();
        $user->setEmail($data['email'] ?? '');
        $user->setNom($data['nom'] ?? '');
        $user->setPrenom($data['prenom'] ?? '');
        $user->setPlainPassword($data['password'] ?? null);

        // 3. Valider. Remplace à la fois tes empty() et ta requête d'unicité :
        //    UniqueEntity fait le SELECT pour toi.
        $erreurs = $validator->validate($user);

        if (count($erreurs) > 0) {
            $details = [];
            foreach ($erreurs as $erreur) {
                $details[$erreur->getPropertyPath()][] = $erreur->getMessage();
            }

            return $this->json(['errors' => $details], 400);
        }

        // 4. Hasher. L'objet est passé en argument parce que la configuration
        //    password_hashers est branchée sur son interface.
        $user->setPassword($hasher->hashPassword($user, $user->getPlainPassword()));
        $user->eraseCredentials();

        // 5. persist() met l'objet en file d'attente, flush() exécute l'INSERT.
        $em->persist($user);
        $em->flush();

        // 6. L'id est renseigné par Doctrine après le flush.
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
        ], 201);
    }
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
        $token = null;

        if ($email) {
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $token = bin2hex(random_bytes(32));

                $reset = new ResetMdp();
                $reset->setToken(hash('sha256', $token));
                $reset->setExpiration(new \DateTimeImmutable('+1 hour'));
                $reset->setCreationMdp(new \DateTimeImmutable());
                $reset->setUtilisateur($user);

                $em->persist($reset);
                $em->flush();
            }
        }

        return $this->json([
            'message' => 'Si un compte existe avec cette adresse, un lien de réinitialisation a été envoyé.',
            // TEMPORAIRE — à retirer quand Mailer sera configuré
            'token' => $token,
        ]);
    }
       #[Route('/api/reset-password', name: 'api_reset_password', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        ValidatorInterface $validator,
    ): JsonResponse {
       
   
        // ÉTAPE 2 — Lire et vérifier les données envoyées par le client.

        // $request représente la requête HTTP reçue.
        // getContent() renvoie le corps brut de cette requête, sous forme de texte :
        //   '{"token":"abc123","password":"MotDePasse123"}'
        //
        // json_decode() transforme ce texte en structure PHP.
        // Le deuxième argument "true" demande un tableau associatif plutôt qu'un objet,
        // ce qui permet d'écrire $data['token'] au lieu de $data->token.
        $data = json_decode($request->getContent(), true);

        // Si le texte reçu n'était pas du JSON valide, json_decode() renvoie null.
        // is_array() vérifie qu'on a bien obtenu un tableau.
        // Le "!" signifie "non", donc cette condition se lit :
        //   "si $data n'est PAS un tableau, alors..."
        //
        // Sans ce test, la ligne suivante planterait sur une valeur null,
        // avec un message d'erreur incompréhensible pour le client.
        if (!is_array($data)) {
            // $this->json() construit une réponse HTTP au format JSON.
            // Le 400 est le code HTTP "Bad Request" : la requête du client est mal formée.
            return $this->json(['error' => 'Corps de requête JSON invalide.'], 400);
        }

        // On extrait les deux valeurs dont on a besoin.
        //
        // L'opérateur ?? se lit "sinon" : si la clé 'token' existe dans $data,
        // on prend sa valeur ; si elle n'existe pas, on met null.
        // Sans ce ??, PHP émettrait un avertissement "clé indéfinie".
        $tokenRecu = $data['token'] ?? null;
        $nouveauMdp = $data['password'] ?? null;

        // On vérifie que les deux valeurs sont bien présentes.
        // Le || signifie "ou" : la condition est vraie si l'une OU l'autre manque.
        //
        // Attention : en PHP, !$variable est vrai pour null, pour une chaîne vide,
        // et aussi pour la chaîne "0". Ici ça convient, un token ou un mot de passe
        // ne valent jamais "0".
        if (!$tokenRecu || !$nouveauMdp) {
            return $this->json(['error' => 'Token et mot de passe requis.'], 400);
        }

        // TEMPORAIRE — sert uniquement à vérifier que la lecture fonctionne.
        // Sera remplacé par la suite du traitement à l'étape 3.
                // ÉTAPE 3 — Retrouver la demande de réinitialisation correspondante.

        // Rappel : lors de forgot-password, on a envoyé le token EN CLAIR au client,
        // mais on a stocké son empreinte SHA-256 en base.
        // Pour retrouver la ligne, il faut donc recalculer cette empreinte.
        //
        // hash('sha256', 'abc') donne toujours le même résultat :
        // c'est une fonction déterministe, contrairement au hachage de mot de passe
        // qui ajoute un sel aléatoire à chaque appel.
        // C'est ce qui permet de s'en servir comme clé de recherche.
        $tokenHache = hash('sha256', $tokenRecu);

        // getRepository() donne accès aux méthodes de recherche pour une entité.
        // findOneBy() cherche UNE ligne correspondant aux critères passés en tableau.
        // Si rien ne correspond, il renvoie null (et non une erreur).
        $reset = $em->getRepository(ResetMdp::class)->findOneBy([
            'token' => $tokenHache,
        ]);

        // ÉTAPE 4 — Vérifier que la demande existe et n'a pas expiré.

        // Deux cas d'échec possibles :
        //   1. $reset vaut null : aucun jeton ne correspond (inventé, ou déjà utilisé).
        //   2. La date d'expiration est passée : le lien est trop vieux.
        //
        // Le || signifie "ou" : il suffit qu'une des deux conditions soit vraie.
        //
        // PHP sait comparer deux objets de date avec < et > : il compare les instants.
        // getExpiration() renvoie la date enregistrée lors de forgot-password,
        // new \DateTimeImmutable() sans argument donne l'instant présent.
        // Donc "expiration < maintenant" se lit : "la date limite est déjà dépassée".
        if (!$reset || $reset->getExpiration() < new \DateTimeImmutable()) {

            // Un SEUL message pour les deux cas, volontairement.
            // Distinguer "jeton inconnu" de "jeton expiré" renseignerait un attaquant
            // sur la validité des jetons qu'il teste. On reste vague.
            return $this->json(['error' => 'Lien invalide ou expiré.'], 400);
        }

        // Si on arrive ici, c'est que $reset existe ET qu'il est encore valide.
        // PHP n'exécute la suite que si la condition ci-dessus était fausse.

        // getUtilisateur() suit la relation ManyToOne déclarée dans l'entité ResetMdp.
        // Doctrine va chercher la ligne correspondante dans UTILISATEUR
        // et te rend un objet User complet.
        $user = $reset->getUtilisateur();

        // ÉTAPE 5 — Valider le nouveau mot de passe.

        // On pose le mot de passe en clair sur l'objet User.
        // Rappel : cette propriété n'est pas enregistrée en base (pas de #[ORM\Column]).
        // Elle existe uniquement pour porter la valeur le temps de la validation.
        $user->setPlainPassword($nouveauMdp);

        // validate() déclenche toutes les contraintes #[Assert\...] de l'entité
        // et renvoie la liste des violations trouvées.
        //
        // Intérêt : la règle "au moins 8 caractères" est écrite une seule fois,
        // dans l'entité, et s'applique ici comme à l'inscription.
        // Si tu changes la règle plus tard, les deux routes suivent automatiquement.
        $erreurs = $validator->validate($user);

        // count() compte les violations. Zéro signifie que tout est valide.
        if (count($erreurs) > 0) {
            $details = [];

            // foreach parcourt chaque violation trouvée.
            // getPropertyPath() donne le nom du champ fautif ('plainPassword' ici).
            // getMessage() donne le message défini dans l'attribut Assert.
            //
            // Les crochets [] à la fin ajoutent au tableau : si un même champ
            // a plusieurs erreurs, elles s'accumulent au lieu de s'écraser.
            foreach ($erreurs as $erreur) {
                $details[$erreur->getPropertyPath()][] = $erreur->getMessage();
            }

            return $this->json(['errors' => $details], 400);
        }

        // TEMPORAIRE — confirme que la validation est passée.
        // ÉTAPE 6 — Enregistrer le nouveau mot de passe et invalider le jeton.

        // hashPassword() transforme le mot de passe en clair en empreinte sécurisée.
        // L'objet $user est passé en premier argument parce que la configuration
        // password_hashers de security.yaml est branchée sur son interface :
        // c'est le type de l'objet qui détermine l'algorithme appliqué.
        //
        // Le résultat est posé sur la propriété $password, celle qui va en base.
        $user->setPassword($hasher->hashPassword($user, $nouveauMdp));

        // eraseCredentials() vide la propriété $plainPassword.
        // Le mot de passe en clair ne doit pas traîner en mémoire plus longtemps
        // que nécessaire, et surtout pas être sérialisé quelque part par accident.
        $user->eraseCredentials();

        // remove() marque la ligne pour suppression.
        // C'est ce qui rend le lien à usage unique : une fois le mot de passe changé,
        // le même lien ne fonctionnera plus, même s'il n'a pas encore expiré.
        //
        // Sans cette ligne, quelqu'un qui retrouverait l'email dans une boîte mail
        // pourrait rechanger le mot de passe pendant l'heure de validité.
        $em->remove($reset);

        // flush() exécute tout d'un coup : l'UPDATE sur UTILISATEUR
        // et le DELETE sur RESET_MDP, dans une seule transaction.
        //
        // Pas besoin de persist() pour $user : Doctrine le suit déjà,
        // puisqu'il vient d'être chargé depuis la base. Il détecte les
        // modifications automatiquement au moment du flush.
        $em->flush();

        return $this->json([
            'message' => 'Mot de passe modifié avec succès.',
        ]);
         }
}
