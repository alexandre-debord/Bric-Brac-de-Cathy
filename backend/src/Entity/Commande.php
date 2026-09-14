<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "COMMANDE")]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_commande", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "statut_commande", type: "string", length: 25, nullable: true)]
    private ?string $statutCommande = null;

    #[ORM\Column(name: "total_ht", type: "decimal", precision: 10, scale: 2, nullable: true)]
    private ?string $totalHt = null;

    #[ORM\Column(name: "taux_tva", type: "decimal", precision: 5, scale: 2, nullable: true)]
    private ?string $tauxTva = null;

    #[ORM\Column(name: "date_commande", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $dateCommande = null;

    #[ORM\Column(name: "modification", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $modification = null;

    #[ORM\ManyToOne(targetEntity: Adresse::class)]
    #[ORM\JoinColumn(name: "id_adresse_facturation", referencedColumnName: "id_adresse", nullable: true)]
    private ?Adresse $adresseFacturation = null;

    #[ORM\ManyToOne(targetEntity: Adresse::class)]
    #[ORM\JoinColumn(name: "id_adresse_livraison", referencedColumnName: "id_adresse", nullable: false)]
    private Adresse $adresseLivraison;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "id_utilisateur", referencedColumnName: "id_utilisateur", nullable: false)]
    private User $utilisateur;

    // getters/setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatutCommande(): ?string
    {
        return $this->statutCommande;
    }

    public function setStatutCommande(?string $statutCommande): static
    {
        $this->statutCommande = $statutCommande;

        return $this;
    }

    public function getTotalHt(): ?string
    {
        return $this->totalHt;
    }

    public function setTotalHt(?string $totalHt): static
    {
        $this->totalHt = $totalHt;

        return $this;
    }

    public function getTauxTva(): ?string
    {
        return $this->tauxTva;
    }

    public function setTauxTva(?string $tauxTva): static
    {
        $this->tauxTva = $tauxTva;

        return $this;
    }

    public function getDateCommande(): ?\DateTime
    {
        return $this->dateCommande;
    }

    public function setDateCommande(?\DateTime $dateCommande): static
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    public function getModification(): ?\DateTime
    {
        return $this->modification;
    }

    public function setModification(?\DateTime $modification): static
    {
        $this->modification = $modification;

        return $this;
    }

    public function getAdresseFacturation(): ?Adresse
    {
        return $this->adresseFacturation;
    }

    public function setAdresseFacturation(?Adresse $adresseFacturation): static
    {
        $this->adresseFacturation = $adresseFacturation;

        return $this;
    }

    public function getAdresseLivraison(): ?Adresse
    {
        return $this->adresseLivraison;
    }

    public function setAdresseLivraison(?Adresse $adresseLivraison): static
    {
        $this->adresseLivraison = $adresseLivraison;

        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }
}