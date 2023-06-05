<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{

    const TYPE_ENTREE = 'entree';
    const TYPE_SORTIE = 'sortie';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    public ?Reference $reference = null;

    #[ORM\Column(length: 255)]
    public string $type;

    #[ORM\Column]
    #[Assert\Positive]
    public ?int $quantite = null;

    #[ORM\Column(nullable: true)]
    public ?float $prix = null;

    #[ORM\ManyToOne(inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    public ?Panier $panier = null;

    public function getDebit(): float
    {
        return ($this->type === self::TYPE_SORTIE) ? $this->quantite * $this->prix : 0;
    }

    public function getCredit(): float
    {
        return ($this->type === self::TYPE_ENTREE) ? $this->quantite * $this->prix : 0;
    }

}
