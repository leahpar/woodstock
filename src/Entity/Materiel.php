<?php

namespace App\Entity;

use App\Logger\LoggableEntity;
use App\Repository\MaterielRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MaterielRepository::class)]
class Materiel extends LoggableEntity
{

    public const CATEGORIES = [
        "Electroportatif",
        "Petit outillage",
        "Echafaudage",
        "Outillage filaire",
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    public ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $reference = null;

    #[ORM\ManyToOne(inversedBy: 'materiels')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    public ?User $proprietaire = null;

    #[ORM\OneToMany(mappedBy: 'materiel', targetEntity: Pret::class)]
    #[ORM\OrderBy(['datePret' => 'DESC', 'id' => 'DESC'])]
    private Collection $prets;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $categorie = null;

    public function __construct()
    {
        $this->prets = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '[' . $this->reference . '] ' . $this->nom;
    }

    /**
     * @return Collection<int, Pret>
     */
    public function getPrets(): Collection
    {
        return $this->prets;
    }

    public function getPretEnCours(): ?Pret
    {
        return $this->prets->filter(fn(Pret $pret) => $pret->dateRetour === null)->first() ?: null;
    }

    public function addPret(Pret $pret): self
    {
        if (!$this->prets->contains($pret)) {
            $this->prets->add($pret);
            $pret->materiel = $this;
        }
        return $this;
    }

    public function removePret(Pret $pret): self
    {
        $this->prets->removeElement($pret);
        return $this;
    }

    public function getPret(): ?Pret
    {
        return $this->prets[0]?->dateRetour === null ? $this->prets[0] : null;
    }

    public function getEmprunteur(): ?User
    {
        return $this->getPret()?->user;
    }


}
