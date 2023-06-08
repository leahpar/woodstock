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
            $key = ($chantier?->id ?? "XX")."-".$stock->reference->codeComptaCompte;

            if (!isset($data[$key])) {
                $data[$key] = [
                    /* 0 Journal    */ "ANA",
                    /* 1 Date       */ $stock->panier->date->format('t/m/Y'),
                    /* 2 Cpte       */ $stock->reference->codeComptaCompte,
                    /* 3 Analytique */ $chantier?->referenceTravaux,
                    /* 4 Libellé    */ $stock->type . " stock du " . $stock->panier->date->format('t/m/Y'),
                    /* 5 Débit      */ $stock->getDebit() ?: null,
                    /* 6 Crédit     */ 0, // $stock->getCredit() ?: null, // Ligne de crédit ajoutée plus bas
                ];
            }
            else {
                $data[$key][5] += $stock->getDebit();
                //$data[$key][6] += $stock->getCredit();
            }
        }

        // Ajout des lignes de crédit (= copie des sorties en mettant le débit au crédit)
        foreach ($data as $key => $datum) {
            $key2 = $key. "-CREDIT";
            $datum[3] = "7000";
            $datum[6] = $datum[5];
            $datum[5] = 0;
            $data[$key2] = $datum;
        }

        // Tri
        ksort($data);

        $filename = "export-";
        $filename .= $date->format('Ym');
        $filename .= ".xlsx";

        // Log
        $this->log("export_compta", null, ['mois' => $date->format('m/Y')]);
        $em->flush();

        return $exportService->exportComptable($data, $filename);

    }
}
