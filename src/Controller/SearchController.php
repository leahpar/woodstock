<?php

namespace App\Controller;

use App\Entity\Chantier;
use App\Entity\Materiel;
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

        if (strlen($search) < 3) {
            return $this->redirectToRoute('dashboard');
        }

        $chantiers = [];
        if ($this->isGranted('ROLE_CHANTIER_LIST')) {
            $chantiers = $em->getRepository(Chantier::class)->findBySearch($search);
        }

        $references = [];
        if ($this->isGranted('ROLE_REFERENCE_LIST')) {
            $references = $em->getRepository(Reference::class)->findBySearch($search);
        }

        $materiels = [];
        if ($this->isGranted('ROLE_MATERIEL_LIST')) {
            $materiels = $em->getRepository(Materiel::class)->findBySearch($search);
        }

        $users = [];
        if ($this->isGranted('ROLE_USER_LIST')) {
            $users = $em->getRepository(User::class)->findBySearch($search);
        }

        return $this->render('search/index.html.twig', [
            'search' => $search,
            'chantiers' => $chantiers,
            'references' => $references,
            'materiels' => $materiels,
            'users' => $users,
        ]);
    }

}
