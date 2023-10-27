<?php

namespace App\Search;

class ChantierSearch extends SearchableEntitySearch
{
    use HydrateTrait;

    public ?string $nom = null;
    public ?string $refTravaux = null;
    public ?string $refBe = null;
    public ?string $cdtTravaux = null;
    public ?bool $enCours = null;

}
