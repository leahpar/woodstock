<?php

namespace App\Search;

class MaterielSearch extends SearchableEntitySearch
{
    use HydrateTrait;

    public ?string $categorie = null;

}
