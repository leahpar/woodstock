<?php

namespace App\Controller;

use App\Entity\Chantier;
use App\Entity\Reference;
use App\Entity\User;
use App\Repository\ChantierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request,
                           EntityManagerInterface $em
    ) {
        $search = $request->query->get('search');

        $chantiers = $em->getRepository(Chantier::class)->findBySearch($search);
        $references = $em->getRepository(Reference::class)->findBySearch($search);
        $users = $em->getRepository(User::class)->findBySearch($search);

        return $this->render('search/index.html.twig', [
            'search' => $search,
            'chantiers' => $chantiers,
            'references' => $references,
            'users' => $users,
        ]);
    }

}
