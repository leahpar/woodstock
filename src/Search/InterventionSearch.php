<?php

namespace App\Search;

use App\Entity\User;

class InterventionSearch extends SearchableEntitySearch
{
    public ?int $semaine = null;
    public ?string $dateStart = null;
    public ?string $dateEnd = null;

    public ?string $equipe = null;
    public ?User $poseur = null;

    public function setSemaine(int $s)
    {
        $this->semaine = $s;
        $this->dateStart = (new \DateTime("1/1 +$s week"))->modify('monday this week')->format('Y-m-d');
        $this->dateEnd   = (new \DateTime("1/1 +$s week"))->modify('sunday this week')->format('Y-m-d');
    }
}
