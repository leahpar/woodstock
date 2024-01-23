<?php

namespace App\Entity;

use App\Logger\LoggableEntity;
use App\Repository\ChantierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChantierRepository::class)]
#[ORM\hasLifecycleCallbacks]
class Chantier extends LoggableEntity
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

    #[ORM\OneToMany(mappedBy: 'chantier', targetEntity: Panier::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OrderBy(['id' => 'DESC'])]
    #[Serializer\Ignore]
    public Collection $paniers;

    #[ORM\ManyToOne(inversedBy: 'chantiers')]
    public ?User $conducteurTravaux = null;

    #[ORM\OneToMany(mappedBy: 'chantier', targetEntity: Intervention::class)]
    #[ORM\OrderBy(['date' => 'DESC'])]
    private Collection $interventions;

    #[ORM\Column()]
    public int $heuresDevisAtelier = 0;

    #[ORM\Column()]
    public int $heuresDevisPose = 0;

    #[ORM\Column()]
    public float $tauxHoraire = 50;

    public function __construct()
    {
        $this->paniers = new ArrayCollection();
        $this->interventions = new ArrayCollection();
    }

    public function addPanier(Panier $panier): self
    {
        if (!$this->paniers->contains($panier)) {
            $this->paniers->add($panier);
            $panier->chantier = $this;
        }

        return $this;
    }

    public function removePanier(Panier $panier): self
    {
        if ($this->paniers->removeElement($panier)) {
            $panier->chantier = null;
        }

        return $this;
    }

    public function __toString(): string
    {
        return implode(' - ', [$this->referenceTravaux, $this->nom]);
    }

    public function getPrix(): float
    {
        return array_reduce(
            $this->paniers->toArray(),
            fn (float $total, Panier $panier) => $total + $panier->getPrix(),
            0
        );
    }

    /**
     * @return Collection<int, Intervention>
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    public function getHeures(string $truc, string $type): int
    {
        // $truc = 'devis', 'planifie' ou 'passe'
        // $type = 'atelier' ou 'pose'
        if ($truc == 'devis') {
            return $this->{'heuresDevis'.ucfirst($type)};
        }
        return array_reduce(
            $this->interventions->toArray(),
            fn (int $total, Intervention $int)
                => $total + ($type && $int->type == $type ? $int->{'heures'.ucfirst($truc).'es'} : 0),
            0
        );
    }

    public function getMontant(string $truc, string $type): int
    {
        // $truc = 'devis', 'planifie' ou 'passe'
        // $type = 'atelier' ou 'pose'
        if ($truc == 'devis') {
            return $this->{'heuresDevis'.ucfirst($type)} * $this->tauxHoraire;
        }
        return array_reduce(
            $this->interventions->toArray(),
            fn (int $total, Intervention $int)
                => $total + ($type && $int->type == $type ? $int->{'heures'.ucfirst($truc).'es'} * $int->tauxHoraire : 0),
            0
        );
    }

}
