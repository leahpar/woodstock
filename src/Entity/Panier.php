<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    public string $type;

    #[ORM\Column]
    public bool $brouillon = true;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    #[ORM\JoinColumn(nullable: false)]
    public User $user;

    #[ORM\OneToMany(mappedBy: 'panier', targetEntity: Stock::class)]
    #[ORM\OrderBy(['id' => 'DESC'])]
    public Collection $stocks;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->date = new \DateTimeImmutable();
    }

    public function addStock(Stock $stock): self
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->panier = $this;
        }
        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        if ($this->stocks->removeElement($stock)) {
            $stock->panier = null;
        }
        return $this;
    }

}
