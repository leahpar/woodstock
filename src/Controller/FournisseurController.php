<?php

namespace App\Controller;

use App\Entity\Fournisseur;
use App\Entity\User;
use App\Form\FournisseurType;
use App\Search\FournisseurSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/fournisseurs')]
class FournisseurController extends CommonController
{
    #[Route('/', name: 'fournisseur_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $search = new FournisseurSearch($request->query->all());
        $fournisseurs = $em->getRepository(Fournisseur::class)->search($search);
        return $this->render('fournisseur/index.html.twig', [
            'fournisseurs' => $fournisseurs,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'fournisseur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $fournisseur = new Fournisseur();

        $form = $this->createForm(FournisseurType::class, $fournisseur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($fournisseur);
            $em->flush(); // pour avoir l'id

            $this->log('create', $fournisseur);
            $em->flush();

            return $this->redirectToRoute('fournisseur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('fournisseur/edit.html.twig', [
            'fournisseur' => $fournisseur,
            'form' => $form,
        ]);
    }

    #[Route('/{id:fournisseur}/edit', name: 'fournisseur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Fournisseur $fournisseur, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(FournisseurType::class, $fournisseur);
        $form->handleRequest($request);

        if ($request->isMethod("GET")) {
            $referer = $request->headers->get('referer');
            $form->get('_referer')->setData($referer);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->log('update', $fournisseur);
            $em->flush();

            if ($form->get('_referer')->getData()) {
                return $this->redirect($form->get('_referer')->getData());
            }
            return $this->redirectToRoute('fournisseur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('fournisseur/edit.html.twig', [
            'fournisseur' => $fournisseur,
            'form' => $form,
        ]);
    }

    #[Route('/{id:fournisseur}', name: 'fournisseur_show', methods: ['GET'])]
    public function show(Fournisseur $fournisseur): Response
    {
        return $this->render('fournisseur/show.html.twig', [
            'fournisseur' => $fournisseur,
        ]);
    }

    #[Route('/{id:fournisseur}', name: 'fournisseur_delete', methods: ['POST'])]
    public function delete(Request $request, Fournisseur $fournisseur, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$fournisseur->id, $request->request->get('_token'))) {
            $em->remove($fournisseur);
            $this->log('delete', $fournisseur);
            $em->flush();
        }

        return $this->redirectToRoute('fournisseur_index', [], Response::HTTP_SEE_OTHER);
    }
}