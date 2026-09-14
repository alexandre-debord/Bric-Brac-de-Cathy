<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "UTILISATEUR")]
#[UniqueEntity(fields: ['email'], message: "Cet email est déjà utilisé.")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_utilisateur", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "Format d'email invalide.")]
    #[Assert\Length(max: 255, maxMessage: "L'email ne peut pas dépasser {{ limit }} caractères.")]
    private string $email;

    #[ORM\Column(name: "password_hash", type: "string", length: 255)]
    private string $password;

    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Length(min: 8, minMessage: "Le mot de passe doit faire au moins {{ limit }} caractères.")]
    private ?string $plainPassword = null;

    #[ORM\Column(type: "string", length: 20)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(max: 20, maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères.")]
    private string $nom;

    #[ORM\Column(type: "string", length: 20)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Length(max: 20, maxMessage: "Le prénom ne peut pas dépasser {{ limit }} caractères.")]
    private string $prenom;
    private const DEFAULT_ROLE = 'user';

    #[ORM\Column(type: "string", length: 20, options: ["default" => "user"])]
    private string $role = self::DEFAULT_ROLE;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }
    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }
    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    // --- Exigé par UserInterface ---
    public function getUserIdentifier(): string
    {
        return $this->email;
    }



    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }
    // --- Exigé par PasswordAuthenticatedUserInterface ---


    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }
    /**
     * Ramène un rôle brut à sa forme canonique : minuscules, sans préfixe ROLE_.
     * Statique et privée : elle ne lit aucun état de l'objet, et doit pouvoir
     * servir avant que la propriété $role ne soit initialisée.
     */
    private static function normalizeRole(string $role): string
    {
        $normalized = strtolower(trim($role));

        return preg_replace('/^role_/', '', $normalized);
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $normalized = self::normalizeRole($role);

        if (!preg_match('/^[a-z_]+$/', $normalized)) {
            throw new \InvalidArgumentException(sprintf('Rôle invalide : « %s »', $role));
        }

        $this->role = $normalized;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];
        $normalized = self::normalizeRole($this->role);

        if (preg_match('/^[a-z_]+$/', $normalized)) {
            $roles[] = 'ROLE_' . strtoupper($normalized);
        }

        return array_values(array_unique($roles));
    }
}
