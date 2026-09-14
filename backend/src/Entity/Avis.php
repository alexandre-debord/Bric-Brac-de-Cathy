<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "AVIS")]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_avis", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "notation", type: "integer", nullable: true)]
    private ?int $notation = null;

    #[ORM\Column(name: "commentaire", type: "text", nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(name: "creation_avis", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $creationAvis = null;

    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: "id_produit", referencedColumnName: "id_produit", nullable: false, onDelete: "CASCADE")]
    private Produit $produit;

    #[ORM\ManyToOne(targetEntity: Commande::class)]
    #[ORM\JoinColumn(name: "id_commande", referencedColumnName: "id_commande", nullable: false, onDelete: "CASCADE")]
    private Commande $commande;

    // getters/setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotation(): ?int
    {
        return $this->notation;
    }

    public function setNotation(?int $notation): static
    {
        $this->notation = $notation;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getCreationAvis(): ?\DateTime
    {
        return $this->creationAvis;
    }

    public function setCreationAvis(?\DateTime $creationAvis): static
    {
        $this->creationAvis = $creationAvis;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;

        return $this;
    }
}