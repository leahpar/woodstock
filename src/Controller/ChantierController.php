<?php

namespace App\Controller;

use App\Entity\Chantier;
use App\Form\ChantierType;
use App\Repository\ChantierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/chantiers')]
class ChantierController extends CommonController
{
    #[Route('/', name: 'chantier_index', methods: ['GET'])]
    #[IsGranted('ROLE_CHANTIER_LIST')]
    public function index(ChantierRepository $chantierRepository): Response
    {
        return $this->render('chantier/index.html.twig', [
            'chantiers' => $chantierRepository->findAll(),
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

        if ($form->isSubmitted() && $form->isValid()) {

            $this->log('update', $chantier);
            $em->flush();

            return $this->redirectToRoute('chantier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('chantier/edit.html.twig', [
            'chantier' => $chantier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'chantier_show', methods: ['GET'])]
    #[IsGranted('ROLE_CHANTIER_LIST')]
    public function show(Chantier $chantier): Response
    {
        return $this->render('chantier/show.html.twig', [
            'chantier' => $chantier,
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
