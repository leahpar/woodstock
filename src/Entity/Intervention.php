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
    public ?int $heures = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ?User $poseur = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    public ?Chantier $chantier = null;

    //TODO Intervention pas chantier

    public function __toString(): string
    {
        return implode(' - ', [
            $this->date->format('d/m/Y'),
            $this->poseur,
            $this->chantier ?? 'autre',
            $this->heures . 'h',
        ]);
    }

    public function toLog(): string
    {
        return implode(' - ', [
            $this->date->format('d/m/Y'),
            $this->poseur->nom,
            $this->heures . 'h',
        ]);
    }
    public function toLogArray(): array
    {
        return [
            //'poseur' => $this->poseur->nom,
            //'heures' => $this->heures . 'h',
            'chantier' => $this->chantier ? $this->chantier->nom : 'autre',
        ];
    }

}
