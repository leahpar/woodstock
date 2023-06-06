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
        $stocks = $em->getRepository(Stock::class)->findByMois($date);

        $data = [];
        /** @var Stock $stock */
        foreach ($stocks as $stock) {
            /** @var Chantier $chantier */
            $chantier = $stock?->panier?->chantier;
            $key = ($chantier?->id ?? "XX")."-".$stock->reference->codeComptaCompte;

            if (!isset($data[$key])) {
                $data[$key] = [
                    /* Journal    */ "ANA",
                    /* Date       */ $stock->panier->date->format('t/m/Y'),
                    /* Cpte       */ $stock->reference->codeComptaCompte,
                    /* Analytique */ $chantier?->referenceTravaux,
                    /* Libellé    */ $stock->type . " stock du " . $stock->panier->date->format('t/m/Y'),
                    /* Débit      */ $stock->getDebit() ?: null,
                    /* Crédit     */ $stock->getCredit() ?: null,
                ];
            }
            else {
                $data[$key][5] += $stock->getDebit();
                $data[$key][6] += $stock->getCredit();
            }
        }

        $filename = "export-";
        $filename .= $date->format('Ym');
        $filename .= ".xlsx";

        // Log
        $this->log("export_compta", null, ['mois' => $date->format('m/Y')]);
        $em->flush();

        return $exportService->exportComptable($data, $filename);

    }
}
