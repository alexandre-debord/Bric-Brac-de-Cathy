<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ]
)]
#[ORM\Entity]
#[ORM\Table(name: "COULEUR")]
class Couleur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_couleur", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "choix_couleur", type: "string", length: 50, nullable: true)]
    private ?string $choixCouleur = null;
    // getters/setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChoixCouleur(): ?string
    {
        return $this->choixCouleur;
    }

    public function setChoixCouleur(?string $choixCouleur): static
    {
        $this->choixCouleur = $choixCouleur;

        return $this;
    }
}