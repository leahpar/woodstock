<?php

namespace App\Search;

class FournisseurSearch extends SearchableEntitySearch
{
    use HydrateTrait;
    
    public ?string $nom = null;
    public ?string $codeComptable = null;
}
