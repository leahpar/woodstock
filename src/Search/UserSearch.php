<?php

namespace App\Search;

use App\Entity\User;

class UserSearch extends SearchableEntitySearch
{
    use HydrateTrait;

    public ?string $nom = null;
    public ?string $prenom = null;
    public ?string $equipe = null;
    public ?bool $chefEquipe = null;
    public ?bool $conducteurTravaux = null;
    public ?bool $disabled = null;
    public ?User $poseur = null;

}
