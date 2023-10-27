<?php

namespace App\Search;

class PretSearch extends SearchableEntitySearch
{
    use HydrateTrait;

    public ?bool $enCours = null;
    public ?string $equipe = null;

}
