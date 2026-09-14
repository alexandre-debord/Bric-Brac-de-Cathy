<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "DECLINAISON")]
class Declinaison
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_declinaison", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "decimal", precision: 15, scale: 2, nullable: true)]
    private ?string $prix = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $stock = null;

    #[ORM\ManyToOne(targetEntity: Produit::class, inversedBy: "declinaisons")]
    #[ORM\JoinColumn(name: "id_produit", referencedColumnName: "id_produit", nullable: false, onDelete: "CASCADE")]
    private Produit $produit;

    #[ORM\ManyToOne(targetEntity: Couleur::class)]
    #[ORM\JoinColumn(name: "id_couleur", referencedColumnName: "id_couleur", nullable: false)]
    private Couleur $couleur;
    // getters/setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(?string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;

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

    public function getCouleur(): ?Couleur
    {
        return $this->couleur;
    }

    public function setCouleur(?Couleur $couleur): static
    {
        $this->couleur = $couleur;

        return $this;
    }
}