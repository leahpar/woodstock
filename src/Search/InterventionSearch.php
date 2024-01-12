<?php

namespace App\Search;

use App\Entity\User;

class InterventionSearch extends SearchableEntitySearch
{
    public ?int $annee = null;
    public ?int $semaine = null;
    public ?int $mois = null;
    public ?string $dateStart = null;
    public ?string $dateEnd = null;

    public ?string $equipe = null;
    public ?User $poseur = null;

    public function setSemaine(int $s)
    {
        $this->semaine = $s;
        if ($s == 0) return;
        $y = $this->annee ?? date('Y');
        // NB: Utilisation du setISODate() pour gérer correctement les semaines
        $this->dateStart = (new \DateTime())->setISODate($y, $s)->modify('monday this week')->format('Y-m-d');
        $this->dateEnd   = (new \DateTime())->setISODate($y, $s)->modify('sunday this week')->format('Y-m-d');
    }

    public function setMois(int $m)
    {
        $this->mois = $m;
        if ($m == 0) return;
        $y = $this->annee ?? date('Y');
        $this->dateStart = (new \DateTime("$y-$m-01"))->format('Y-m-01'); // 1er jour du mois
        $this->dateEnd   = (new \DateTime("$y-$m-01"))->format('Y-m-t');  // dernier jour du mois
    }

    public function getSemaineSuivante(): array
    {
        $date = (new \DateTime($this->dateStart))->modify('+1 week');
        return [
            'semaine' => $date->format('W'),
            'annee' => $date->format('Y'),
        ];
    }

    public function getSemainePrecedente(): array
    {
        $date = (new \DateTime($this->dateStart))->modify('-1 week');
        return [
            'semaine' => $date->format('W'),
            'annee' => $date->format('Y'),
        ];
    }

    public function getMoisSuivant(): array
    {
        $date = (new \DateTime($this->dateStart))->modify('+1 month');
        return [
            'mois' => $date->format('m'),
            'annee' => $date->format('Y'),
        ];
    }

    public function getMoisPrecedent(): array
    {
        $date = (new \DateTime($this->dateStart))->modify('-1 month');
        return [
            'mois' => $date->format('m'),
            'annee' => $date->format('Y'),
        ];
    }
}
