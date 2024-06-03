<?php

namespace App\Entity;

use App\Repository\EpiRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EpiRepository::class)]
class Epi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    public ?string $nom = null;

    #[ORM\Column(type: 'date')]
    public ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'epis')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?User $user = null;

    public function __toString(): string
    {
        return $this->nom
            . ' (' . $this->date->format('Y-m-d')
            . ' - ' . $this->user
            . ')';
    }

}
