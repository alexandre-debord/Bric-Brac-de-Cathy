<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "PRODUIT")]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_produit", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "nom_du_produit", type: "string", length: 50)]
    private string $nomDuProduit;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: "creation_produit", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $creationProduit = null;

    #[ORM\ManyToOne(targetEntity: Categorie::class)]
    #[ORM\JoinColumn(name: "id_categorie", referencedColumnName: "id_categorie", nullable: false)]
    private Categorie $categorie;

    #[ORM\OneToMany(mappedBy: "produit", targetEntity: Declinaison::class, cascade: ["remove"], orphanRemoval: true)]
    private Collection $declinaisons;

    #[ORM\OneToMany(mappedBy: "produit", targetEntity: Image::class, cascade: ["remove"], orphanRemoval: true)]
    private Collection $images;

    public function __construct()
    {
        $this->declinaisons = new ArrayCollection();
        $this->images = new ArrayCollection();
    }
    // getters/setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomDuProduit(): ?string
    {
        return $this->nomDuProduit;
    }

    public function setNomDuProduit(string $nomDuProduit): static
    {
        $this->nomDuProduit = $nomDuProduit;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreationProduit(): ?\DateTime
    {
        return $this->creationProduit;
    }

    public function setCreationProduit(?\DateTime $creationProduit): static
    {
        $this->creationProduit = $creationProduit;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection<int, Declinaison>
     */
    public function getDeclinaisons(): Collection
    {
        return $this->declinaisons;
    }

    public function addDeclinaison(Declinaison $declinaison): static
    {
        if (!$this->declinaisons->contains($declinaison)) {
            $this->declinaisons->add($declinaison);
            $declinaison->setProduit($this);
        }

        return $this;
    }

    public function removeDeclinaison(Declinaison $declinaison): static
    {
        if ($this->declinaisons->removeElement($declinaison)) {
            // set the owning side to null (unless already changed)
            if ($declinaison->getProduit() === $this) {
                $declinaison->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProduit($this);
        }

        return $this;
    }

    public function removeImage(Image $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProduit() === $this) {
                $image->setProduit(null);
            }
        }

        return $this;
    }
}