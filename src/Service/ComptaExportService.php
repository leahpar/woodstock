<?php

namespace App\Service;

use App\Entity\Chantier;
use App\Entity\Intervention;
use App\Entity\Stock;
use App\Logger\LoggerService;
use Doctrine\ORM\EntityManagerInterface;

class ComptaExportService
{
    public function __construct(
        private readonly ExportService $exportService,
        private readonly EntityManagerInterface $em,
        protected readonly LoggerService $log,
    ){}

    private function exportComptableStock(\DateTime $mois)
    {
        $stocks = $this->em->getRepository(Stock::class)->findSortiesByMois($mois);

        $data = [];
        /** @var Stock $stock */
        foreach ($stocks as $stock) {
            /** @var Chantier $chantier */
            $chantier = $stock?->panier?->chantier;

            $key  = ($chantier?->referenceTravaux??'7001');
            $key .= $stock->reference->codeComptaCompte;
            $key .= ($stock->isEntree()) ? "E" : "S";
            //dump('--------------');
            //dump(key: $key, credit: $stock->getCredit(), debit: $stock->getDebit());
            if (!isset($data[$key])) {
                $data[$key] = [
                    /* 0 Journal    */ "ANA",
                    /* 1 Date       */ $stock->panier->date->format('t/m/Y'),
                    /* 2 Cpte       */ $stock->reference->codeComptaCompte,
                    /* 3 Analytique */ $chantier?->referenceTravaux??'7001',
                    /* 4 Libellé    */ /*$stock->type .*/ "Sortie stock du " . $stock->panier->date->format('t/m/Y'),
                    /* 5 Débit      */ $stock->getDebit()  ?: 0,
                    /* 6 Crédit     */ $stock->getCredit() ?: 0,
                ];
            }
            else {
                $data[$key][5] += $stock->getDebit();
                $data[$key][6] += $stock->getCredit();
            }

            $key  = "7000";
            $key .= $stock->reference->codeComptaCompte;
            $key .= ($stock->isEntree()) ? "S" : "E"; // Inversé !
            //dump(key: $key, credit: $stock->getCredit(), debit: $stock->getDebit());
            if (!isset($data[$key])) {
                $data[$key] = [
                    /* 0 Journal    */ "ANA",
                    /* 1 Date       */ $stock->panier->date->format('t/m/Y'),
                    /* 2 Cpte       */ $stock->reference->codeComptaCompte,
                    /* 3 Analytique */ '7000',
                    /* 4 Libellé    */ /*$stock->type .*/ "Sortie stock du " . $stock->panier->date->format('t/m/Y'),
                    // NB: colonnes inversées !
                    /* 5 Débit      */ $stock->getCredit() ?: 0,
                    /* 6 Crédit     */ $stock->getDebit()  ?: 0,
                ];
            }
            else {
                // NB: colonnes inversées !
                $data[$key][5] += $stock->getCredit();
                $data[$key][6] += $stock->getDebit();
            }
        }

        // Tri
        ksort($data);

        $filename = "export-stock-";
        $filename .= $mois->format('Ym');
        $filename .= ".xlsx";

        return $this->exportService->exportComptable($data, $filename);
    }

    private function exportComptableHeures(\DateTime $mois)
    {
        $stocks = $this->em->getRepository(Intervention::class)->findByMois($mois);

        if (count($stocks) === 0) {
            throw new \Exception("Aucune intervention ce mois-ci");
        }

        $data = [];
        /** @var Intervention $intervention */
        foreach ($stocks as $intervention) {

            // #34 : intervention non validée => on bloque l'export
            if (!$intervention->valide) {
                throw new \Exception("Il y a des intervention(s) non validée(s)");
            }

            /** @var Chantier $chantier */
            $chantier = $intervention?->chantier;

            $key = ($chantier?->referenceTravaux??'7000');
            if (!isset($data[$key])) {
                $data[$key] = [
                    /* 0 Journal    */ "ANA",
                    /* 1 Date       */ $intervention->date->format('t/m/Y'),
                    /* 2 Cpte       */ "64110000",
                    /* 3 Analytique */ $chantier?->referenceTravaux??'7000',
                    /* 4 Libellé    */ "Intervention",
                    /* 5 Débit      */ $intervention->getPrix() ?: 0,
                    /* 6 Crédit     */ $intervention->getPrix() ?: 0,
                ];
            }
            else {
                $data[$key][5] += $intervention->getPrix();
                $data[$key][6] += $intervention->getPrix();
            }
        }

        // Tri
        ksort($data);

        $filename = "export-heures-";
        $filename .= $mois->format('Ym');
        $filename .= ".xlsx";

        return $this->exportService->exportComptable($data, $filename);
    }

    private function exportChantiers()
    {
        $chantiers = $this->em->getRepository(Chantier::class)->findBy(['encours' => true]);

        $data = [];
        /** @var Chantier $chantier */
        foreach ($chantiers as $chantier) {
            $data[$chantier->referenceTravaux] = [
                $chantier->nom,
                $chantier->referenceTravaux,
                $chantier->budgetAchat,
                $chantier->getHeures('devis', 'atelier'),
                $chantier->getMontant('devis', 'atelier'),
                $chantier->getHeures('devis', 'pose'),
                $chantier->getMontant('devis', 'pose'),
                $chantier->getHeures('passe', 'atelier'),
                $chantier->getMontant('passe', 'atelier'),
                $chantier->getHeures('passe', 'pose'),
                $chantier->getMontant('passe', 'pose'),
                $chantier->getBudgetRestant(),
                $chantier->getHeures('restant', 'atelier'),
                $chantier->getMontant('restant', 'atelier'),
                $chantier->getHeures('restant', 'pose'),
                $chantier->getMontant('restant', 'pose'),
                $chantier->getRestantTotal(),
            ];
        }

        // Tri
        ksort($data);
        dump($data);

        $filename = "export-chantiers-";
        $filename .= date('Ymd-His');
        $filename .= ".xlsx";

        return $this->exportService->exportChantiers($data, $filename);
    }

    public function exportComptable(string $action, \DateTime $date)
    {
        switch ($action) {
            case 'stock':
                return $this->exportComptableStock($date);
            case 'heures':
                return $this->exportComptableHeures($date);
            case 'chantiers':
                return $this->exportChantiers();
            default:
                throw new \Exception("Action non supportée");
        }
    }
}
