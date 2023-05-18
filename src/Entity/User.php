<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table]
#[UniqueEntity(fields: ['username'], message: 'Un autre utilisateur existe déjà avec ce nom d\'utilisateur.')]

class User implements UserInterface, PasswordAuthenticatedUserInterface
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

    #[ORM\Column(type: 'datetime')]
    public \DateTime $updatedAt;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Panier::class, orphanRemoval: true)]
    private Collection $paniers;

    public function __construct()
    {
        $this->updatedAt = new \DateTime();
        $this->paniers = new ArrayCollection();
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
        if ($this->paniers->removeElement($panier)) {
            $panier->user = null;
        }

        return $this;
    }

}
