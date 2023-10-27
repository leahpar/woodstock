<?php

namespace App\Controller;

use App\Entity\Chantier;
use App\Entity\Materiel;
use App\Entity\Reference;
use App\Entity\User;
use App\Repository\ChantierRepository;
use App\Search\ChantierSearch;
use App\Search\ReferenceSearch;
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
        $searchStr = $request->query->get('search');
        if (strlen($searchStr) < 3) {
            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }

        $chantiers = [];
        if ($this->isGranted('ROLE_CHANTIER_LIST')) {
            $chantiers = $em->getRepository(Chantier::class)->search(
                new ChantierSearch([...$request->query->all(), 'limit' => 0])
            );
        }

        $references = [];
        if ($this->isGranted('ROLE_REFERENCE_LIST')) {
            $references = $em->getRepository(Reference::class)->search(
                new ReferenceSearch([...$request->query->all(), 'limit' => 0])
            );
        }

        $materiels = [];
        if ($this->isGranted('ROLE_MATERIEL_LIST')) {
            $search = $request->query->get('search');
            $materiels = $em->getRepository(Materiel::class)->findBySearch($search);
        }

        $users = [];
        if ($this->isGranted('ROLE_USER_LIST')) {
            $search = $request->query->get('search');
            $users = $em->getRepository(User::class)->findBySearch($search);
        }

        return $this->render('search/index.html.twig', [
            'gsearch' => $searchStr,
            'chantiers' => $chantiers,
            'references' => $references,
            'materiels' => $materiels,
            'users' => $users,
        ]);
    }

}
