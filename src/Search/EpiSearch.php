<?php

namespace App\Search;

class EpiSearch extends SearchableEntitySearch
{
    use HydrateTrait;

    public ?string $nom = null;
    public ?string $user = null;

}
