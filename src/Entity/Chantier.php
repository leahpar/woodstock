<?php

namespace App\Entity;

use App\Repository\ChantierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChantierRepository::class)]
class Chantier
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    public string $nom;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    public string $referenceTravaux;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $referenceEtude = null;

    #[ORM\Column]
    public bool $encours = true;

    #[ORM\Column(nullable: true)]
    public ?string $commentaire = null;

}
