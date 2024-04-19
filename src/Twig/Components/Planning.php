<?php

namespace App\Twig\Components;

use App\Entity\Intervention;
use App\Entity\User;
use App\Search\InterventionSearch;
use App\Search\UserSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent(name:'planning', template: 'planning/planning.html.twig')]
class Planning
{

    private array $interventions = [];
    private array $poseurs = [];
    private array $equipes = [];
    private array $dates = [];
    private array $totalHeuresPlanifieesPoseurs = [];
    private array $totalHeuresPasseesPoseurs = [];

    public function __construct(
        private readonly EntityManagerInterface $em,
    ){}

    public function mount($interventions, InterventionSearch $search): void
    {
        $date = new \DateTime($search->dateStart);
        $end = new \DateTime($search->dateEnd);
        while ($date <= $end) {
            if ($date->format('N') <= 6) {
                $this->dates[$date->format('Y-m-d')] = clone $date;
            }
            $date->modify('+1 day');
        }

        /** @var Intervention $intervention */
        foreach ($interventions as $intervention) {
            $poseur = $intervention->poseur;
            $date = $intervention->date->format('Y-m-d');
            $this->interventions[$poseur->id][$date][] = $intervention;
            if ($intervention->valide) {
                $this->dates[$date]->valide = true;
            }
        }

        if ($search->poseur) {
            $poseurs = [$search->poseur];
        }
        else {
            // NB: on prend même les poseurs désactivés ici, ils seront masqués plus tard si pas d'intervention
            $uSearch = new UserSearch(array_merge(
                $search->toArray(),
                ['tri' => 'equipe', 'masquerPlanning' => false],
            ));
            $poseurs = $this->em->getRepository(User::class)->search($uSearch)
                ->getIterator()->getArrayCopy();
        }

        $this->poseurs = array_combine(
            array_map(fn(user $p) => $p->id, $poseurs),
            $poseurs
        );

        foreach ($this->poseurs as $id => $poseur) {
            $this->totalHeuresPlanifieesPoseurs[$id] = 0;
            $this->totalHeuresPasseesPoseurs[$id] = 0;
            $this->interventions[$id] ??= [];
            foreach ($this->interventions[$id] as $interventions) {
                $this->totalHeuresPlanifieesPoseurs[$id] += array_reduce(
                    $interventions,
                    fn($t, Intervention $i) => $t + $i->heuresPlanifiees,
                    0
                );
                $this->totalHeuresPasseesPoseurs[$id] += array_reduce(
                    $interventions,
                    fn($t, Intervention $i) => $t + $i->heuresPassees,
                    0
                );
            }
        }

        // on supprime les poseurs désactivés & sans intervention
        foreach ($this->poseurs as $id => $poseur) {
            if ($poseur->disabled
                && $this->totalHeuresPlanifieesPoseurs[$id] == 0
                && $this->totalHeuresPasseesPoseurs[$id] == 0) {
                unset($this->poseurs[$id]);
                unset($this->interventions[$id]);
                unset($this->totalHeuresPlanifieesPoseurs[$id]);
                unset($this->totalHeuresPasseesPoseurs[$id]);
            }
        }

        $this->equipes = array_unique(array_map(fn(User $p) => $p->equipe, $this->poseurs));

    }

    #[ExposeInTemplate]
    public function getPoseurs(): array
    {
        return $this->poseurs;
    }

    #[ExposeInTemplate]
    public function getEquipes(): array
    {
        return $this->equipes;
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
    public function getTotalHeuresPlanifieesPoseurs(): array
    {
        return $this->totalHeuresPlanifieesPoseurs;
    }

    #[ExposeInTemplate]
    public function getTotalHeuresPasseesPoseurs(): array
    {
        return $this->totalHeuresPasseesPoseurs;
    }

}
