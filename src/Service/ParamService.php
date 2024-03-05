<?php

namespace App\Service;

use App\Entity\Intervention;
use App\Entity\Param;
use App\Entity\Reference;
use App\Logger\LoggerService;
use Doctrine\ORM\EntityManagerInterface;

class ParamService
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LoggerService $log,
    ) {}

    public function get(string $nom): ?string
    {
        $param = $this->em->getRepository(Param::class)->findOneBy(['nom' => $nom]);
        return $param?->valeur;
    }

    public function saveParam(string $nom, mixed $valeur)
    {
        $param = $this->em->getRepository(Param::class)->findOneBy(['nom' => $nom]) ?? new Param($nom);
        $this->em->persist($param);

        // Si pas de changement, on ne fait rien
        if ($valeur == $param->valeur) return;

        $param->valeur = (string)$valeur;

        if (str_starts_with($param->nom, "prixm3")) {
            $this->majPrixm3($param);
        }

        if (str_starts_with($param->nom, "taux_horaire_")) {
            $this->majTauxHoraire($param);
        }

        $this->log->log('parametrage', null, [
            $nom => $param->valeur,
        ]);
    }

    private function majPrixm3(Param $param)
    {
        $essence = substr($param->nom, 7);
        $references = $this->em->getRepository(Reference::class)->findBy(['essence' => $essence]);
        foreach ($references as $ref) {
            $ref->prixm3 = $param->valeur;
//            dump($ref->nom, $ref->prixm3, $ref->getVolume(), $ref->calcPrix());
            // MAJ du prix si volume défini
            if ($ref->getVolume() > 0) {
                $ref->prix = $ref->calcPrix();
            }
        }
    }

    private function majTauxHoraire(Param $param)
    {
        // taux_horaire_2024
        $annee = substr($param->nom, 13);
        $taux = $param->valeur;

        $interventions = $this->em->getRepository(Intervention::class)->findByAnnee($annee);
        foreach ($interventions as $intervention) {
            $intervention->tauxHoraire = $taux;
        }
    }
}
