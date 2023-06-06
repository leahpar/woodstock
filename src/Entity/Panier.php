<?php

namespace App\Entity;

use App\Logger\LoggableEntity;
use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier extends LoggableEntity
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column()]
    public ?\DateTime $date = null;

    #[ORM\Column(length: 255)]
    public string $type;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $commentaire = null;

    #[ORM\Column]
    #[Ignore]
    public bool $brouillon = true;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    public User $user;

    #[ORM\ManyToOne()]
    public ?User $poseur = null;

    #[ORM\OneToMany(mappedBy: 'panier', targetEntity: Stock::class)]
    #[ORM\OrderBy(['id' => 'DESC'])]
    #[Ignore]
    public Collection $stocks;

    // Pour le formulaire E/S
    public Stock $stock;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    public ?Chantier $chantier = null;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
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

    public function __toString(): string
    {
        return $this->type == Stock::TYPE_SORTIE ? 'Sortie' : 'Entrée'
            . ' - ' . $this->date->format('d/m/Y H:i')
            . ' - ' . $this->user->username;
    }

}
