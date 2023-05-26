<?php

namespace App\Entity;

use App\Logger\LoggableEntity;
use App\Repository\NotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(nullable: true)]
    public ?string $role = null;

    #[ORM\Column]
    public ?string $message = null;

    #[ORM\Column(type: 'datetime')]
    public ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $dateTraite = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?User $user = null;

    #[ORM\Column(nullable: true)]
    public ?string $class = null;

    #[ORM\Column(nullable: true)]
    public ?int $target = null;

    // Pour affichage
    public ?LoggableEntity $entity = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'pings')]
    #[ORM\JoinTable(name: 'notification_ping')]
    private Collection $pings;

    public function __construct(?LoggableEntity $entity = null)
    {
        $this->date = new \DateTime();

        // Pour avoir la bonne classe, au lieu du proxy en cache (Proxies\__CG__\App\Entity\*)
        $this->class = preg_replace("/.*(App\\\\Entity\\\\.*)/", "$1", get_class($entity));
        $this->target = $entity->id;
        $this->pings = new ArrayCollection();
    }

    public function getEntityType(): ?string
    {
        if ($this->class === null) return null;
        return strtolower(preg_replace("/.*\\\\(.*)$/", "$1", $this->class));
    }

    /**
     * @return Collection<int, User>
     */
    public function getPings(): Collection
    {
        return $this->pings;
    }

    public function addPing(User $ping): self
    {
        if (!$this->pings->contains($ping)) {
            $this->pings->add($ping);
        }

        return $this;
    }

    public function removePing(User $ping): self
    {
        $this->pings->removeElement($ping);

        return $this;
    }
}
