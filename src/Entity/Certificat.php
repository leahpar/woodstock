<?php

namespace App\Entity;

use App\Logger\LoggableEntity;
use App\Repository\CertificatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CertificatRepository::class)]
class Certificat extends LoggableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    public ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'certificats')]
    #[ORM\JoinColumn(nullable: false)]
    public ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    public ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    public ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\PositiveOrZero]
    public ?int $alerteNb = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Choice(['D', 'W', 'M', 'Y'])]
    public ?string $alertePeriode = null;

    #[ORM\Column(type: 'boolean')]
    public bool $alerte = false;

    #[ORM\OneToMany(mappedBy: 'certificat', targetEntity: Media::class, orphanRemoval: true)]
    public Collection $medias;

    public function __construct()
    {
        $this->medias = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nom
            . ' ' . $this->user->nom
            . ' (' . ($this->dateDebut ? $this->dateDebut->format('d/m/Y') : '...')
            . ' > ' . ($this->dateFin  ? $this->dateFin->format('d/m/Y')   : '...')
            . ')';
    }

    public function isExpire(): bool
    {
        return $this->dateFin && $this->dateFin < new \DateTime();
    }

    public function dateAlerteExpiration(): ?\DateTime
    {
        if (!$this->alerteNb || !$this->alertePeriode || !$this->dateFin) {
            return null;
        }
        $interval = new \DateInterval('P' . $this->alerteNb . $this->alertePeriode);
        return (clone $this->dateFin)->sub($interval);
    }

}
