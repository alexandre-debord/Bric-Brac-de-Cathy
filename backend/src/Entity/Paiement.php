<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "PAIEMENT")]
class Paiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_paiement", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "id_transaction", type: "string", length: 100, unique: true)]
    private string $idTransaction;

    #[ORM\Column(name: "methode", type: "string", length: 30, nullable: true)]
    private ?string $methode = null;

    #[ORM\Column(name: "status", type: "string", length: 30, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(name: "creation_paiement", type: "datetime_immutable", nullable: true)]
    private ?\DateTimeInterface $creationPaiement = null;

    #[ORM\Column(name: "montant", type: "decimal", precision: 10, scale: 2, nullable: true)]
    private ?string $montant = null;

    #[ORM\ManyToOne(targetEntity: Commande::class)]
    #[ORM\JoinColumn(name: "id_commande", referencedColumnName: "id_commande", nullable: false, unique: true)]
    private Commande $commande;

    // getters/setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdTransaction(): ?string
    {
        return $this->idTransaction;
    }

    public function setIdTransaction(string $idTransaction): static
    {
        $this->idTransaction = $idTransaction;

        return $this;
    }

    public function getMethode(): ?string
    {
        return $this->methode;
    }

    public function setMethode(?string $methode): static
    {
        $this->methode = $methode;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreationPaiement(): ?\DateTimeInterface
    {
        return $this->creationPaiement;
    }

    public function setCreationPaiement(?\DateTimeInterface $creationPaiement): static
    {
        $this->creationPaiement = $creationPaiement;

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(?string $montant): static
    {
        $this->montant = $montant;

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