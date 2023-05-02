<?php

namespace App\Controller;

use App\Entity\Reference;
use App\Entity\Stock;
use App\Form\ReferenceType;
use App\Form\StockEntreeType;
use App\Repository\ReferenceRepository;
use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/references')]
class ReferenceController extends AbstractController
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
    public function new(Request $request, ReferenceRepository $referenceRepository): Response
    {
        $reference = new Reference();
        $form = $this->createForm(ReferenceType::class, $reference);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $referenceRepository->save($reference, true);

            return $this->redirectToRoute('reference_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reference/edit.html.twig', [
            'reference' => $reference,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'reference_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_REFERENCE_EDIT')]
    public function edit(Request $request, Reference $reference, ReferenceRepository $referenceRepository): Response
    {
        $form = $this->createForm(ReferenceType::class, $reference);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $referenceRepository->save($reference, true);

            return $this->redirectToRoute('reference_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reference/edit.html.twig', [
            'reference' => $reference,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'reference_show', methods: ['GET'])]
    #[IsGranted('ROLE_REFERENCE_LIST')]
    public function show(Reference $reference): Response
    {
        return $this->render('reference/show.html.twig', [
            'reference' => $reference,
        ]);
    }

    #[Route('/{id}', name: 'reference_delete', methods: ['POST'])]
    #[IsGranted('ROLE_REFERENCE_EDIT')]
    public function delete(Request $request, Reference $reference, ReferenceRepository $referenceRepository): Response
    {
        $referenceRepository->remove($reference, true);
        return $this->redirectToRoute('reference_index', [], Response::HTTP_SEE_OTHER);
    }

}
