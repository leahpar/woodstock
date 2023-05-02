<?php

namespace App\Entity;

use App\Repository\ReferenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReferenceRepository::class)]
class Reference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\Unique]
    public string $reference;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    public string $nom;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    public string $marque;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $conditionnement = null;

    #[ORM\OneToMany(mappedBy: 'reference', targetEntity: Stock::class, orphanRemoval: true)]
    public Collection $stocks;

    #[ORM\Column(nullable: true)]
    public ?int $seuil = null;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
    }

    public function addStock(Stock $stock): self
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->reference = $this;
        }

        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        if ($this->stocks->removeElement($stock)) {
            $stock->reference = null;
        }
        return $this;
    }

    public function getQuantite(): int
    {
        $quantite = 0;
        foreach ($this->stocks as $stock) {
            if ($stock->type === Stock::TYPE_ENTREE) {
                $quantite += $stock->quantite;
            } else {
                $quantite -= $stock->quantite;
            }
        }
        return $quantite;
    }

    public function __toString(): string
    {
        return "[$this->reference] $this->nom ($this->conditionnement)";
    }

}
