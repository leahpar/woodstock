<?php

namespace App\Search;

use App\Entity\Reference;

class ReferenceSearch extends SearchableEntitySearch
{
    use HydrateTrait;

    public ?string $categorie = null;
}
