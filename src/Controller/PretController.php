<?php

namespace App\Controller;

use App\Entity\Pret;
use App\Search\PretSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PretController extends CommonController
{
    #[Route('/prets', name: 'pret_index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $search = new PretSearch([
            ...$request->query->all(),
            'enCours' => true,
        ]);
        $prets = $em->getRepository(Pret::class)->search($search);
        return $this->render('pret/index.html.twig', [
            'prets' => $prets,
            'search' => [
                'page' => $search->page,
                'limit' => $search->limit,
                'count' => $prets->count(),
            ],
        ]);
    }

}
