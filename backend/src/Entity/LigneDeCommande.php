<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "LIGNE_DE_COMMANDE")]
class LigneDeCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_ligne_de_commande", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "quantite", type: "integer", nullable: true)]
    private ?int $quantite = null;

    #[ORM\Column(name: "prix_unit", type: "decimal", precision: 15, scale: 2, nullable: true)]
    private ?string $prixUnit = null;

    #[ORM\ManyToOne(targetEntity: Declinaison::class)]
    #[ORM\JoinColumn(name: "id_declinaison", referencedColumnName: "id_declinaison", nullable: false)]
    private Declinaison $declinaison;

    #[ORM\ManyToOne(targetEntity: Commande::class)]
    #[ORM\JoinColumn(name: "id_commande", referencedColumnName: "id_commande", nullable: false, onDelete: "CASCADE")]
    private Commande $commande;

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

    public function getPrixUnit(): ?string
    {
        return $this->prixUnit;
    }

    public function setPrixUnit(?string $prixUnit): static
    {
        $this->prixUnit = $prixUnit;

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