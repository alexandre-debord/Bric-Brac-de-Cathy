<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "RESET_MDP")]
#[ORM\UniqueConstraint(name: "uk_reset_token", columns: ["token"])]
class ResetMdp
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_reset_mdp", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 100)]
    private string $token;

    #[ORM\Column(name: "expiration", type: "datetime_immutable")]
    private \DateTimeInterface $expiration;

    #[ORM\Column(name: "creation_mdp", type: "datetime_immutable", nullable: true)]
    private ?\DateTimeInterface $creationMdp = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "id_utilisateur", referencedColumnName: "id_utilisateur", nullable: false, onDelete: "CASCADE")]
    private User $utilisateur;

    // getters/setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;
        return $this;
    }

    public function getExpiration(): \DateTimeInterface
    {
        return $this->expiration;
    }

    public function setExpiration(\DateTimeInterface $expiration): static
    {
        $this->expiration = $expiration;
        return $this;
    }

      public function getCreationMdp(): ?\DateTimeInterface
    {
        return $this->creationMdp;
    }

    public function setCreationMdp(?\DateTimeInterface $creationMdp): static
    {
        $this->creationMdp = $creationMdp;
        return $this;
    }

    public function getUtilisateur(): User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }
}
