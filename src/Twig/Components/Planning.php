<?php

namespace App\Twig\Components;

use App\Entity\Intervention;
use App\Entity\User;
use App\Search\UserSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent(name:'planning', template: 'planning/planning.html.twig')]
class Planning
{

    private array $interventions = [];
    private array $poseurs = [];
    private array $dates = [];
    private array $totalHeuresPoseurs = [];

    public function __construct(
        private readonly EntityManagerInterface $em,
    ){}

    public function mount($interventions, string $dateStart, string $dateEnd): void
    {
        /** @var Intervention $intervention */
        foreach ($interventions as $intervention) {
            $poseur = $intervention->poseur;
            $date = $intervention->date->format('Y-m-d');
            $this->interventions[$poseur->id][$date][] = $intervention;
        }

        $date = new \DateTime($dateStart);
        $end = new \DateTime($dateEnd);
        while ($date <= $end) {
            if ($date->format('N') < 6) {
                $this->dates[$date->format('Y-m-d')] = clone $date;
            }
            $date->modify('+1 day');
        }

        $poseurs = $this->em->getRepository(User::class)->search(
            new UserSearch(['page' => 1, 'limit' => 0])
        )->getIterator()->getArrayCopy();

        $this->poseurs = array_combine(
            array_map(fn(user $p) => $p->id, $poseurs),
            $poseurs
        );

        foreach ($this->poseurs as $id => $poseur) {
            $this->totalHeuresPoseurs[$id] = 0;
            $this->interventions[$id] ??= [];
            foreach ($this->interventions[$id] as $interventions) {
                $this->totalHeuresPoseurs[$id] += array_reduce($interventions, fn($t, Intervention $i) => $t + $i->heures, 0);
            }
        }
    }

    #[ExposeInTemplate]
    public function getPoseurs(): array
    {
        return $this->poseurs;
    }

    #[ExposeInTemplate]
    public function getDates(): array
    {
        return $this->dates;
    }

    #[ExposeInTemplate]
    public function getInterventions(): array
    {
        return $this->interventions;
    }

    #[ExposeInTemplate]
    public function getTotalHeuresPoseurs(): array
    {
        return $this->totalHeuresPoseurs;
    }

}
