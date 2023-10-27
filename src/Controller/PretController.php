<?php

namespace App\Controller;

use App\Entity\Pret;
use App\Search\PretSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PretController extends CommonController
{
    #[Route('/prets', name: 'pret_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $search = new PretSearch([
            'enCours' => true,
        ]);
        $prets = $em->getRepository(Pret::class)->search($search);
        return $this->render('pret/index.html.twig', [
            'prets' => $prets,
        ]);
    }

}
