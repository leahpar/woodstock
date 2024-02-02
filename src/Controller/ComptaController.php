<?php

namespace App\Controller;

use App\Entity\Chantier;
use App\Entity\Stock;
use App\Export\ExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/compta')]
class ComptaController extends CommonController
{
    #[Route('/export', name: 'compta_export')]
    public function export(Request $request, EntityManagerInterface $em, ExportService $exportService)
    {
        $date = new \DateTime($request->request->get('date'));
        $stocks = $em->getRepository(Stock::class)->findSortiesByMois($date);

        $data = [];
        /** @var Stock $stock */
        foreach ($stocks as $stock) {
            /** @var Chantier $chantier */
            $chantier = $stock?->panier?->chantier;

            dump($stock);
            $key = ($chantier->referenceTravaux?:'7001')."-".$stock->reference->codeComptaCompte;
            if (!isset($data[$key])) {
                $data[$key] = [
                    /* 0 Journal    */ "ANA",
                    /* 1 Date       */ $stock->panier->date->format('t/m/Y'),
                    /* 2 Cpte       */ $stock->reference->codeComptaCompte,
                    /* 3 Analytique */ $chantier->referenceTravaux?:'7001',
                    /* 4 Libellé    */ $stock->type . " stock du " . $stock->panier->date->format('t/m/Y'),
                    /* 5 Débit      */ $stock->getDebit()  ?: 0,
                    /* 6 Crédit     */ $stock->getCredit() ?: 0,
                ];
            }
            else {
                $data[$key][5] += $stock->getDebit();
                $data[$key][6] += $stock->getCredit();
            }

            $key .= "-CREDIT";
            if (!isset($data[$key])) {
                $data[$key] = [
                    /* 0 Journal    */ "ANA",
                    /* 1 Date       */ $stock->panier->date->format('t/m/Y'),
                    /* 2 Cpte       */ $stock->reference->codeComptaCompte,
                    /* 3 Analytique */ '7000',
                    /* 4 Libellé    */ $stock->type . " stock du " . $stock->panier->date->format('t/m/Y'),
                    // NB: colonnes inversées !
                    /* 5 Débit      */ $stock->getCredit() ?: 0,
                    /* 6 Crédit     */ $stock->getDebit()  ?: 0,
                ];
            }
            else {
                // NB: colonnes inversées !
                $data[$key][6] += $stock->getDebit();
                $data[$key][5] += $stock->getCredit();
            }
        }

        // Tri
        ksort($data);

        $filename = "export-";
        $filename .= $date->format('Ym');
        $filename .= ".csv";

        // Log
        $this->log("export_compta", null, ['mois' => $date->format('m/Y')]);
        $em->flush();

        return $exportService->exportComptable($data, $filename);
    }
}
