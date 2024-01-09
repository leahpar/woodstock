<?php

namespace App\Controller;

use App\Entity\Reference;
use App\Entity\Stock;
use App\Entity\User;
use App\Export\ExportService;
use App\Form\ReferenceType;
use App\Repository\ReferenceRepository;
use App\Search\ReferenceSearch;
use App\Service\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/references')]
class ReferenceController extends CommonController
{
    #[Route('/', name: 'reference_index', methods: ['GET'])]
    #[IsGranted('ROLE_REFERENCE_LIST')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $search = new ReferenceSearch($request->query->all());
        $references = $em->getRepository(Reference::class)->search($search);
        return $this->render('reference/index.html.twig', [
            'references' => $references,
            'search' => [
                'page' => $search->page,
                'limit' => $search->limit,
                'count' => $references->count(),
            ],
        ]);
    }

    #[Route('/catalogue', name: 'reference_catalogue', methods: ['GET'])]
    #[IsGranted('ROLE_REFERENCE_LIST')]
    public function catalogue(Request $request, ReferenceRepository $referenceRepository): Response
    {
        $categories = $request->query->has('categorie')
            ? $referenceRepository->findBy(['categorie' => $request->query->get('categorie')])
            : $referenceRepository->findAll();

        return $this->render('reference/catalogue.html.twig', [
            'references' => $categories,
            'categorie' => $request->query->get('categorie'),
        ]);
    }

    #[Route('/export', name: 'reference_export')]
    #[IsGranted('ROLE_REFERENCE_LIST')]
    public function export(EntityManagerInterface $em, ExportService $exportService)
    {
        $search = new ReferenceSearch(['limit' => 0]);
        $references = $em->getRepository(Reference::class)->search($search);

        $filename = "export-inventaire-";
        $filename .= date('Ymd');
        $filename .= ".xlsx";

        // Log
        $this->log("export_inventaire", null);
        $em->flush();

        return $exportService->exportInventaire($references->getIterator()->getArrayCopy(), $filename);
    }

    #[Route('/new', name: 'reference_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_REFERENCE_EDIT')]
    public function new(Request $request, EntityManagerInterface $em, PanierService $panierService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $reference = new Reference();
        $form = $this->createForm(ReferenceType::class, $reference);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($reference);
            $em->flush(); // pour avoir l'id

            $data = [];
            $quantite = $form->get('quantite')->getData();
            if ($quantite != 0) {
                $panierService->stockRegulReference($reference, $quantite, $user);
                $data['stock'] = $quantite;
            }

            $this->log('create', $reference, $data);
            $em->flush();

            return $this->redirectToRoute('reference_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reference/edit.html.twig', [
            'reference' => $reference,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'reference_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_REFERENCE_EDIT')]
    public function edit(Request $request, Reference $reference, EntityManagerInterface $em, PanierService $panierService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ReferenceType::class, $reference);
        $form->handleRequest($request);

        if ($request->isMethod("GET")) {
            $referer = $request->headers->get('referer');
            $form->get('_referer')->setData($referer);
        }

        if ($form->isSubmitted() && $form->isValid()) {

            // #5 MAJ prix stock rétroactif
            /** @var Stock $stock */
            foreach ($reference->stocks as $stock) {
                $stock->prix = $reference->prix;
            }

            // Gestion du stock
            $data = [];
            $quantite = $form->get('quantite')->getData();
            if ($quantite != $reference->getQuantite()) {
                $quantite = $quantite - $reference->getQuantite();
                $panierService->stockRegulReference($reference, $quantite, $user);
                $data['stock'] = (($quantite>0) ? '+' : '').$quantite;
            }

            $this->log('update', $reference, $data);
            $em->flush();

            if ($form->get('_referer')->getData()) {
                return $this->redirect($form->get('_referer')->getData());
            }
            return $this->redirectToRoute('reference_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reference/edit.html.twig', [
            'reference' => $reference,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'reference_show', methods: ['GET'])]
    #[IsGranted('ROLE_REFERENCE_LIST')]
    public function show(Reference $reference, EntityManagerInterface $em): Response
    {
        $historiqueStocks = $em->getRepository(Stock::class)->getHistoriqueReference($reference);

        $stats = [];
        $stocks = $em->getRepository(Stock::class)->findBy(['reference' => $reference]);
        foreach ($stocks as $stock) {
            // On ne compte pas le volume initial
            // Ni les stocks en brouillon
            if (!$stock->panier || $stock->panier->brouillon) continue;
            $date = $stock->panier->date;

            if (!isset($stats[$date->format('Y')])) $stats[$date->format('Y')] = ['E' => 0, 'S' => 0];
            if (!isset($stats[$date->format('Y-m')])) $stats[$date->format('Y-m')] = ['E' => 0, 'S' => 0];

            $stats[$date->format('Y')]['E'] += $stock->type === Stock::TYPE_ENTREE ? $stock->quantite : 0;
            $stats[$date->format('Y')]['S'] += $stock->type === Stock::TYPE_SORTIE ? $stock->quantite : 0;
            $stats[$date->format('Y-m')]['E'] += $stock->type === Stock::TYPE_ENTREE ? $stock->quantite : 0;
            $stats[$date->format('Y-m')]['S'] += $stock->type === Stock::TYPE_SORTIE ? $stock->quantite : 0;
        }
        // Tri par clé (année / année-mois)
        uksort(
            $stats,
            fn ($a, $b) => str_pad($b, 8, "-99") <=> str_pad($a, 8, "-99")
        );

        // TODO: ChartJS : https://ux.symfony.com/chartjs

        return $this->render('reference/show.html.twig', [
            'reference' => $reference,
            'historiqueStocks' => $historiqueStocks,
            'stats' => $stats,
        ]);
    }

    #[Route('/{id}', name: 'reference_delete', methods: ['POST'])]
    #[IsGranted('ROLE_REFERENCE_EDIT')]
    public function delete(Reference $reference, EntityManagerInterface $em): Response
    {
        $em->remove($reference);
        $this->log('delete', $reference);
        $em->flush();

        return $this->redirectToRoute('reference_index', [], Response::HTTP_SEE_OTHER);
    }


}
