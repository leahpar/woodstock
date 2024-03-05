<?php

namespace App\Controller;

use App\Entity\Chantier;
use App\Entity\Stock;
use App\Form\ChantierType;
use App\Search\ChantierSearch;
use App\Service\ParamService;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/new',       name: 'chantier_new',  defaults: ['action' => 'create'])]
    #[Route('/{id}/edit', name: 'chantier_edit', defaults: ['action' => 'update'])]
    #[IsGranted('ROLE_CHANTIER_EDIT')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        ParamService $paramService,
        string $action,
        ?Chantier $chantier,
    ): Response
    {
        $chantier ??= new Chantier();
        $form = $this->createForm(ChantierType::class, $chantier);
        $form->handleRequest($request);

        if ($action == 'update' && $request->isMethod("GET")) {
            $referer = $request->headers->get('referer');
            $form->get('_referer')->setData($referer);
        }

        if ($action == 'create') {
            $form->get('tauxHoraire')->setData($paramService->get('taux_horaire_'.date('Y')));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($chantier);
            if ($action == 'create') $em->flush(); // pour avoir l'id

            $this->log($action, $chantier);
            $em->flush();

            if ($form->get('_referer')->getData()) {
                return $this->redirect($form->get('_referer')->getData());
            }
            return $this->redirectToRoute('chantier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('chantier/edit.html.twig', [
            'chantier' => $chantier,
            'form' => $form,
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

            $date = $stock->panier->date;
            $cat = $stock->reference->categorie;

            if (!isset($stats["9999-99"][$cat])) $stats["9999-99"][$cat] = 0;
            if (!isset($stats[$date->format('Y-m')][$cat])) $stats[$date->format('Y-m')][$cat] = 0;

            $stats["9999-99"][$cat] += $stock->isSortie() ? $stock->getDebit() : 0;
            $stats[$date->format('Y-m')][$cat] += $stock->isSortie() ? $stock->getDebit() : 0;

            $stats["9999-99"][$cat] -= $stock->isEntree() ? $stock->getCredit() : 0;
            $stats[$date->format('Y-m')][$cat] -= $stock->isEntree() ? $stock->getCredit() : 0;
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
