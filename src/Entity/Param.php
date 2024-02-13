<?php

namespace App\Entity;

use App\Repository\ParamRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParamRepository::class)]
class Param
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    public ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $valeur = null;

    public function __construct(string $nom, $valeur = null)
    {
        $this->nom = $nom;
        if ($valeur !== null) $this->valeur = (string)$valeur;
    }
}
