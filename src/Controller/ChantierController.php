<?php

namespace App\Controller;

use App\Entity\Chantier;
use App\Entity\Stock;
use App\Form\ChantierType;
use App\Repository\ChantierRepository;
use App\Search\ChantierSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/chantiers')]
class ChantierController extends CommonController
{
    #[Route('/', name: 'chantier_index', methods: ['GET'])]
    #[IsGranted('ROLE_CHANTIER_LIST')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $search = new ChantierSearch($request->query->all());
        $chantiers = $em->getRepository(Chantier::class)->search($search);

        return $this->render('chantier/index.html.twig', [
            'chantiers' => $chantiers,
            'search' => [
                'page' => $search->page,
                'limit' => $search->limit,
                'count' => $chantiers->count(),
            ],
        ]);
    }

    #[Route('/new', name: 'chantier_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CHANTIER_EDIT')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $chantier = new Chantier();
        $form = $this->createForm(ChantierType::class, $chantier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($chantier);
            $em->flush(); // pour avoir l'id
            $this->log('create', $chantier);
            $em->flush();

            return $this->redirectToRoute('chantier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('chantier/edit.html.twig', [
            'chantier' => $chantier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'chantier_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CHANTIER_EDIT')]
    public function edit(Request $request, Chantier $chantier, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ChantierType::class, $chantier);
        $form->handleRequest($request);

        if ($request->isMethod("GET")) {
            $referer = $request->headers->get('referer');
            $form->get('_referer')->setData($referer);
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $this->log('update', $chantier);
            $em->flush();

            if ($form->get('_referer')->getData()) {
                return $this->redirect($form->get('_referer')->getData());
            }
            return $this->redirectToRoute('chantier_index', [], Response::HTTP_SEE_OTHER);
            //return $this->redirectToLastSearch(defaultRoute: 'chantier_index');
        }

        return $this->render('chantier/edit.html.twig', [
            'chantier' => $chantier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'chantier_show', methods: ['GET'])]
    #[IsGranted('ROLE_CHANTIER_LIST')]
    public function show(Chantier $chantier, EntityManagerInterface $em): Response
    {
        $stats = [];
        $stocks = $em->getRepository(Stock::class)->findByChantier($chantier);
        /** @var Stock $stock */
        foreach ($stocks as $stock) {

            if ($stock->type === Stock::TYPE_ENTREE) continue;

            $date = $stock->panier->date;
            $cat = $stock->reference->categorie;

            if (!isset($stats["9999-99"][$cat])) $stats["9999-99"][$cat] = 0;
            if (!isset($stats[$date->format('Y-m')][$cat])) $stats[$date->format('Y-m')][$cat] = 0;

            $stats["9999-99"][$cat] += $stock->type === Stock::TYPE_SORTIE ? $stock->getDebit() : 0;
            $stats[$date->format('Y-m')][$cat] += $stock->type === Stock::TYPE_SORTIE ? $stock->getDebit() : 0;
        }
        // Tri par clé (année / année-mois)
        krsort($stats);

        // TODO: ChartJS : https://ux.symfony.com/chartjs


        return $this->render('chantier/show.html.twig', [
            'chantier' => $chantier,
            'stats' => $stats,
        ]);
    }

    #[Route('/{id}', name: 'chantier_delete', methods: ['POST'])]
    #[IsGranted('ROLE_CHANTIER_EDIT')]
    public function delete(Chantier $chantier, EntityManagerInterface $em): Response
    {
        $em->remove($chantier);
        $this->log('delete', $chantier);
        $em->flush();

        return $this->redirectToRoute('chantier_index', [], Response::HTTP_SEE_OTHER);
    }

}
