<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "PANIER_LIGNE")]
class PanierLigne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_panier_ligne", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "quantite", type: "integer", nullable: true)]
    private ?int $quantite = null;

    #[ORM\ManyToOne(targetEntity: Panier::class)]
    #[ORM\JoinColumn(name: "id_panier", referencedColumnName: "id_panier", nullable: false, onDelete: "CASCADE")]
    private Panier $panier;

    #[ORM\ManyToOne(targetEntity: Declinaison::class)]
    #[ORM\JoinColumn(name: "id_declinaison", referencedColumnName: "id_declinaison", nullable: false, onDelete: "CASCADE")]
    private Declinaison $declinaison;

    // getters/setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(?Panier $panier): static
    {
        $this->panier = $panier;

        return $this;
    }

    public function getDeclinaison(): ?Declinaison
    {
        return $this->declinaison;
    }

    public function setDeclinaison(?Declinaison $declinaison): static
    {
        $this->declinaison = $declinaison;

        return $this;
    }
}