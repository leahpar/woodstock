<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{

    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        $start = new \DateTime('2023-01-01');
        $date = new \DateTime();
        $mois = [];
        while ($date > $start) {
            $mois[] = clone $date;
            $date->modify('-1 month');
        }

        return $this->render('dashboard/dashboard.html.twig', [
            'moisLst' => $mois,
        ]);
    }
}
