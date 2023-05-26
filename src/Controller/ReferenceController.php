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

        return $this->render('reference/show.html.twig', [
            'reference' => $reference,
            'historiqueStocks' => $historiqueStocks,
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
