<?php

namespace App\Service;

use App\Entity\Intervention;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class InterventionService
{


    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ){}

    /**
     * Prérempli les donnes de l'intervention
     * avec les paramètres de la requête GET
     */
    public function preremplissage(Intervention &$intervention, array $data)
    {
        $intervention->date = new \DateTime($data['date']??null);
        $poseur = $this->em->getRepository(User::class)->find((int)($data['poseur']??null));
        $intervention->poseur = $poseur;

        $interventions = $this->em->getRepository(Intervention::class)->findBy([
            'date' => $intervention->date,
            'poseur' => $intervention->poseur,
        ]);
        $heuresDispo = array_reduce(
            $interventions,
            fn($acc, $i) => $acc - (($i->id != $intervention->id) ? $i->heuresPlanifiees : 0),
            10
        );
        $intervention->heuresPlanifiees = max(0, $heuresDispo);
    }

    /**
     * Retourne la liste des poseurs sélectionnables pour une intervention
     */
    public function getPoseursSelectionnables(): array
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if ($this->security->isGranted('ROLE_PLANNING_EDIT')) {
            $poseurs = $this->em->getRepository(User::class)->findBy(['disabled' => false]);
        }
        elseif ($user->chefEquipe) {
            $poseurs = $this->em->getRepository(User::class)->findBy(['disabled' => false, 'equipe' => $user->equipe]);
        }
        else {
            $poseurs = [$user];
        }
        return $poseurs;
    }


    public function propagerCreation(Intervention $intervention, array $jours, array $poseurs): array
    {
        $interventions = [];
        $intParente = null;

        // Dupliquer l'intervention pour chaque poseur
        foreach ($poseurs as $poseur) {

            // Intervention parente initiale ou clone pour les suivantes
            $intParente = $intParente ? clone $intervention : $intervention;
            $int = null;

            // Dupliquer l'intervention pour chaque jour sélectionné
            foreach ($jours as $jour) {

                // Intervention initiale ou clone pour les suivantes
                $int = $int ? clone $intParente : $intParente;

                // Intervention chainée
                $int->parent = count($jours) > 1 ? $intParente : null;

                // $jour = 1, 2, 3... (lundi, mardi, mercredi...)
                $int->poseur = $poseur;
                $int->date = (clone $int->date)->modify("monday this week +" . ($jour - 1) . " days");

                $this->em->persist($int);
                $interventions[] = $int;
            }
        }

        return $interventions;
    }

    public function propagerModification(Intervention $intervention): array
    {
        $interventions = [];
        $parent = $intervention->parent;

        /** @var Intervention $int */
        foreach ($parent->enfants??[] as $int) {

            // NB: On touche pas aux interventions validées
            if ($int->valide) continue;

            if ($intervention->id != $int->id) {
                $int->updateFrom($intervention);
            }

            $interventions[] = $int;
        }
        return $interventions;
    }

    public function detach(Intervention $intervention)
    {
        // les AUTRES enfants
        $enfants = $intervention->parent?->enfants->filter(fn (Intervention $i) => $i != $intervention);
        $enfants ??= [];

        // 0 ou 1 enfant => pas de groupe => pas de parent
        // Sinon nouveau parent
        $parent = (count($enfants) < 2) ? null : $enfants->first();

        /** @var Intervention $int */
        foreach ($enfants as $int) {
            $int->parent = $parent;
        }

        $intervention->parent = null;
    }

}
