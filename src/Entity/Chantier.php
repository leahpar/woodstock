<?php

namespace App\Entity;

use App\Logger\LoggableEntity;
use App\Repository\ChantierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;
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
    #[Ignore]
    public Collection $paniers;

    #[ORM\ManyToOne(inversedBy: 'chantiers')]
    public ?User $conducteurTravaux = null;

    #[ORM\OneToMany(mappedBy: 'chantier', targetEntity: Intervention::class)]
    private Collection $interventions;

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

    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->setChantier($this);
        }

        return $this;
    }

    public function removeIntervention(Intervention $intervention): static
    {
        if ($this->interventions->removeElement($intervention)) {
            // set the owning side to null (unless already changed)
            if ($intervention->getChantier() === $this) {
                $intervention->setChantier(null);
            }
        }

        return $this;
    }


}
