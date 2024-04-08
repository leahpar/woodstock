<?php

namespace App\Controller;

use App\Service\ComptaExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/compta')]
class ComptaController extends CommonController
{
    #[Route('/export_stock',  name: 'compta_export_stock', defaults: ['action' => 'stock'])]
    #[Route('/export_heures', name: 'compta_export_heures', defaults: ['action' => 'heures'])]
    #[IsGranted('ROLE_COMPTA')]
    public function exportStock(
        string $action,
        Request $request,
        EntityManagerInterface $em,
        ComptaExportService $comptaExportService,
    ) {
        $date = new \DateTime($request->request->get('date'));

        try {
            $exportResponse = $comptaExportService->exportComptable($action, $date);

            // Log
            $this->log->log("export_compta_$action", null, ['mois' => $date->format('m/Y')]);
            $em->flush();

            return $exportResponse;
        }
        catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('dashboard');
        }
    }

}
