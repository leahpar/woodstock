<?php

namespace App\Controller;

use App\Entity\Reference;
use App\Entity\Stock;
use App\Form\ReferenceType;
use App\Repository\ReferenceRepository;
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
    public function index(ReferenceRepository $referenceRepository): Response
    {
        return $this->render('reference/index.html.twig', [
            'references' => $referenceRepository->findAll(),
        ]);
    }

    #[Route('/catalogue', name: 'reference_catalogue', methods: ['GET'])]
    #[IsGranted('ROLE_REFERENCE_LIST')]
    public function catalogue(ReferenceRepository $referenceRepository): Response
    {
        return $this->render('reference/catalogue.html.twig', [
            'references' => $referenceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'reference_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_REFERENCE_EDIT')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $reference = new Reference();
        $form = $this->createForm(ReferenceType::class, $reference);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($reference);
            $em->flush(); // pour avoir l'id
            $this->log('create', $reference);
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
    public function edit(Request $request, Reference $reference, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ReferenceType::class, $reference);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->log('update', $reference);
            $em->flush();

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
            if (!$stock->panier && $stock->panier->brouillon) continue;
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
