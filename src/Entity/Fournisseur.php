<?php

namespace App\Entity;

use App\Logger\LoggableEntity;
use App\Repository\FournisseurRepository;
use App\Search\HydrateTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FournisseurRepository::class)]
class Fournisseur extends LoggableEntity
{
    use HydrateTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    public ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $telephone = null;

    #[ORM\Column(length: 255)]
    public ?string $codeComptable = null;

    #[ORM\Column(length: 255)]
    public ?string $compteGeneral = null;

    #[ORM\Column(length: 255)]
    public ?string $compteCharge = null;

    #[ORM\Column(length: 255)]
    public ?string $compteTva = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $journalAchat = null;

}
