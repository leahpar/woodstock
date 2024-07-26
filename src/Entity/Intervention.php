<?php

namespace App\Entity;

use App\Logger\LoggableEntity;
use App\Repository\InterventionRepository;
use App\Search\HydrateTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
class Intervention extends LoggableEntity
{
    use HydrateTrait;

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

    // User qui a créé l'intervention (seul lui peut la supprimer)
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?User $auteur = null;

    // Interventions multiples - parent
    #[ORM\ManyToOne(inversedBy: 'enfants')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    public ?Intervention $parent = null;

    // Interventions multiples - enfants
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Intervention::class)]
    #[Ignore]
    public Collection $enfants;

    #[ORM\Column]
    public ?string $activite = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    public ?Chantier $chantier = null;

    // 'Atelier' / 'Pose'
    #[ORM\Column(nullable: true)]
    public ?string $type = null;

    #[ORM\Column(nullable: true)]
    public ?string $commentaire = null;

    #[ORM\Column]
    public float $tauxHoraire = 0;

    #[ORM\Column]
    public bool $valide = false;

    public function __construct()
    {
        $this->enfants = new ArrayCollection();
    }

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

    public function isParent(): bool
    {
        return $this->parent?->id === $this->id;
    }

    public function updateFrom(Intervention $intervention)
    {
        $this->hydrate([
            'heuresPlanifiees' => $intervention->heuresPlanifiees,
            'activite' => $intervention->activite,
            'chantier' => $intervention->chantier,
            'type' => $intervention->type,
            'commentaire' => $intervention->commentaire,
        ]);
    }

    public function getLies()
    {
        return $this->parent?->enfants;
    }

    public function hasEnfantValide(): bool
    {
        if ($this->parent) {
            return $this->getLies()->exists(fn(int $k, Intervention $i) => $i->valide);
        }
        else {
            return $this->valide;
        }
    }

    public function isPassee(?\Datetime $date = null): bool
    {
        return $this->date < ($date ?? new \DateTime());
    }

}
