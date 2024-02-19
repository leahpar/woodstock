<?php

namespace App\Entity;

use App\Logger\LoggableEntity;
use App\Repository\InterventionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
class Intervention extends LoggableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    public ?\DateTime $date = null;

    #[ORM\Column]
    public int $heuresPlanifiees = 0;

    #[ORM\Column]
    public int $heuresPassees = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?User $poseur = null;

    #[ORM\Column]
    public ?string $activite = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    public ?Chantier $chantier = null;

    // 'Atelier' / 'Pose'
    #[ORM\Column(nullable: true)]
    public ?string $type = null;

    #[ORM\Column]
    public float $tauxHoraire = 0;

    #[ORM\Column]
    public bool $valide = false;


    public function __toString(): string
    {
        return implode(' - ', [
            $this->date->format('d/m/Y'),
            $this->poseur,
            $this->activite,
            $this->heuresPlanifiees . 'h',
        ]);
    }

    public function toLog(): string
    {
        return implode(' - ', [
            $this->date->format('d/m/Y'),
            $this->poseur->nom,
            $this->activite,
            $this->heuresPlanifiees . 'h',
        ]);
    }
    public function toLogArray(): array
    {
        return array_filter([
            //'poseur' => $this->poseur->nom,
            //'heures' => $this->heures . 'h',
            'chantier' => ($this->activite == 'chantier') ? $this->chantier?->nom : null,
            'type' => ($this->activite == 'chantier') ? $this->type : null,
        ]);
    }

    public function getPrix(): float
    {
        return $this->heuresPassees * $this->tauxHoraire;
    }

}
