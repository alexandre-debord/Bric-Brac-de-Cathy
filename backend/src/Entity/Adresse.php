<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "ADRESSE")]
class Adresse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_adresse", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "type", type: "string", length: 50, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(name: "rue", type: "string", length: 255, nullable: true)]
    private ?string $rue = null;

    #[ORM\Column(name: "ville", type: "string", length: 50, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(name: "code_postal", type: "string", length: 50, nullable: true)]
    private ?string $codePostal = null;

    #[ORM\Column(name: "pays", type: "string", length: 50, nullable: true)]
    private ?string $pays = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "id_utilisateur", referencedColumnName: "id_utilisateur", nullable: false, onDelete: "CASCADE")]
    private User $utilisateur;

    // getters/setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(?string $rue): static
    {
        $this->rue = $rue;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): static
    {
        $this->pays = $pays;

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