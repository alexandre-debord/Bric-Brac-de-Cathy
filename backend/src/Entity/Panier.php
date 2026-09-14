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

    #[ORM\Column(name: "creation_panier", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $creationPanier = null;

    #[ORM\Column(name: "modification_panier", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $modificationPanier = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "id_utilisateur", referencedColumnName: "id_utilisateur", nullable: false, unique: true, onDelete: "CASCADE")]
    private User $utilisateur;

    // getters/setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreationPanier(): ?\DateTime
    {
        return $this->creationPanier;
    }

    public function setCreationPanier(?\DateTime $creationPanier): static
    {
        $this->creationPanier = $creationPanier;

        return $this;
    }

    public function getModificationPanier(): ?\DateTime
    {
        return $this->modificationPanier;
    }

    public function setModificationPanier(?\DateTime $modificationPanier): static
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