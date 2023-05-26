<?php

namespace App\Entity;

use App\Logger\LoggableEntity;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table]
#[UniqueEntity(fields: ['username'], message: 'Un autre utilisateur existe déjà avec ce nom d\'utilisateur.')]
class User  extends LoggableEntity implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    public ?string $username = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank]
    public ?string $nom = null;

    #[ORM\Column]
    public array $roles = [];

    #[ORM\Column]
    private string $password = '';
    public ?string $plainPassword = null;

    #[ORM\Column(nullable: true)]
    public ?string $equipe = null;

    #[ORM\Column]
    public bool $chefEquipe = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Panier::class, orphanRemoval: true)]
    #[Ignore]
    private Collection $paniers;

    #[ORM\OneToMany(mappedBy: 'proprietaire', targetEntity: Materiel::class)]
    private Collection $materiels;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $updatedAt;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Pret::class, orphanRemoval: true)]
    private Collection $prets;

    #[ORM\ManyToMany(targetEntity: Notification::class, mappedBy: 'pings')]
    private Collection $pings;

    public function __construct()
    {
        $this->paniers = new ArrayCollection();
        $this->materiels = new ArrayCollection();
        $this->updatedAt = new \DateTime();
        $this->prets = new ArrayCollection();
        $this->pings = new ArrayCollection();
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $hashPassword)
    {
        $this->password = $hashPassword;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function __toString(): string
    {
        return $this->nom ?? $this->username;
    }

    public function touch(): void
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return Collection<int, Panier>
     */
    public function getPaniers(): Collection
    {
        return $this->paniers;
    }

    public function addPanier(Panier $panier): self
    {
        if (!$this->paniers->contains($panier)) {
            $this->paniers->add($panier);
            $panier->user = $this;
        }

        return $this;
    }

    public function removePanier(Panier $panier): self
    {
        $this->paniers->removeElement($panier);
        return $this;
    }

    /**
     * @return Collection<int, Materiel>
     */
    public function getMateriels(): Collection
    {
        return $this->materiels;
    }

    public function addMateriel(Materiel $materiel): self
    {
        if (!$this->materiels->contains($materiel)) {
            $this->materiels->add($materiel);
            $materiel->proprietaire = $this;
        }
        return $this;
    }

    public function removeMateriel(Materiel $materiel): self
    {
        if ($this->materiels->removeElement($materiel)) {
            $materiel->proprietaire = null;
        }
        return $this;
    }

    /**
     * @return Collection<int, Pret>
     */
    public function getPrets(): Collection
    {
        return $this->prets;
    }

    public function addPret(Pret $pret): self
    {
        if (!$this->prets->contains($pret)) {
            $this->prets->add($pret);
            $pret->user = $this;
        }

        return $this;
    }

    public function removePret(Pret $pret): self
    {
        if ($this->prets->removeElement($pret)) {
            $pret->user = null;
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getPings(): Collection
    {
        return $this->pings;
    }

    public function hasPing(Notification $notif): bool
    {
        return $this->pings->contains($notif);
    }

    public function addPing(Notification $ping): self
    {
        if (!$this->pings->contains($ping)) {
            $this->pings->add($ping);
            $ping->addPing($this);
        }
        return $this;
    }

    public function removePing(Notification $ping): self
    {
        if ($this->pings->removeElement($ping)) {
            $ping->removePing($this);
        }
        return $this;
    }

}
