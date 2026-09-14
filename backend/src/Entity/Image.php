<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "IMAGE")]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_image", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "url_image", type: "string", length: 250, nullable: true)]
    private ?string $urlImage = null;

    #[ORM\ManyToOne(targetEntity: Produit::class, inversedBy: "images")]
    #[ORM\JoinColumn(name: "id_produit", referencedColumnName: "id_produit", nullable: false, onDelete: "CASCADE")]
    private Produit $produit;
    // getters/setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlImage(): ?string
    {
        return $this->urlImage;
    }

    public function setUrlImage(?string $urlImage): static
    {
        $this->urlImage = $urlImage;

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
}