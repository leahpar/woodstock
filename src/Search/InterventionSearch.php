<?php

namespace App\Search;

use App\Entity\User;

class InterventionSearch extends SearchableEntitySearch
{
    public ?int $annee = null;
    public ?int $semaine = null;
    public int $plage = 1; // Nombre de semaines à afficher
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
        $this->dateStart = (new \DateTime())->setISODate($y, $s)->modify("monday this week")->format('Y-m-d');
        $plage = $this->plage - 1;
        $this->dateEnd   = (new \DateTime())->setISODate($y, $s)->modify("sunday +$plage week")->format('Y-m-d');
    }

    public function getSemaineCourante(): array
    {
        $date = (new \DateTime());
        return [
            'semaine' => $date->format('W'),
            'annee' => $date->format('Y'),
            'plage' => $this->plage,
        ];
    }

    public function getSemaineSuivante(): array
    {
        $date = (new \DateTime($this->dateStart))->modify('+1 week');
        $s = $date->format('W');
        $y = $date->format('Y');
        if ($s == 1) $y++; // Passage à l'année suivante
        return [
            'semaine' => $s,
            'annee' => $y,
            'plage' => $this->plage,
        ];
    }

    public function getSemainePrecedente(): array
    {
        $date = (new \DateTime($this->dateStart))->modify('-1 week');
        return [
            'semaine' => $date->format('W'),
            'annee' => $date->format('Y'),
            'plage' => $this->plage,
        ];
    }

//    public function getMoisSuivant(): array
//    {
//        $date = (new \DateTime($this->dateStart))->modify('+1 month');
//        return [
//            'mois' => $date->format('m'),
//            'annee' => $date->format('Y'),
//        ];
//    }
//
//    public function getMoisPrecedent(): array
//    {
//        $date = (new \DateTime($this->dateStart))->modify('-1 month');
//        return [
//            'mois' => $date->format('m'),
//            'annee' => $date->format('Y'),
//        ];
//    }
}
