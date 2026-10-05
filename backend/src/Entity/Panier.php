<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "PANIER")]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_panier", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "creation_panier", type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $creationPanier = null;

    #[ORM\Column(name: "modification_panier", type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $modificationPanier = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "id_utilisateur", referencedColumnName: "id_utilisateur", nullable: false, unique: true, onDelete: "CASCADE")]
    private User $utilisateur;

    // getters/setters

    public function __construct()
    {
        $this->creationPanier = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreationPanier(): ?\DateTimeImmutable
    {
        return $this->creationPanier;
    }

    public function setCreationPanier(?\DateTimeImmutable $creationPanier): static
    {
        $this->creationPanier = $creationPanier;

        return $this;
    }

    public function getModificationPanier(): ?\DateTimeImmutable
    {
        return $this->modificationPanier;
    }

    public function setModificationPanier(?\DateTimeImmutable $modificationPanier): static
    {
        $this->modificationPanier = $modificationPanier;

        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }
}
