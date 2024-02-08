<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    //#[Route('/', name: 'home')]
    //public function home(): Response
    //{
    //    return $this->redirectToRoute('dashboard');
    //}

    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/dashboard.html.twig');
    }
}
