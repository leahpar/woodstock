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
    #[Route('/export_stock',     name: 'compta_export_stock',     defaults: ['action' => 'stock'],     methods: ['POST'])]
    #[Route('/export_heures',    name: 'compta_export_heures',    defaults: ['action' => 'heures'],    methods: ['POST'])]
    #[Route('/export_chantiers', name: 'compta_export_chantiers', defaults: ['action' => 'chantiers'], methods: ['GET'])]
    #[IsGranted('ROLE_COMPTA')]
    public function exportStock(string $action, Request $request, ComptaExportService $comptaExportService)
    {
        try {
            $date = new \DateTime($request->request->get('date'));
            return $comptaExportService->exportComptable($action, $date);
        }
        catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('dashboard');
        }
    }


}
