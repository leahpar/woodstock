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
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    public ?Reference $reference = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    public ?string $type = null;

    #[ORM\Column]
    #[Assert\Positive]
    public ?int $quantite = 0;

}
