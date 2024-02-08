<?php

namespace App\Controller;

use App\Entity\Certificat;
use App\Form\CertificatType;
use App\Search\CertificatSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/certificats')]
class CertificatController extends CommonController
{
    #[Route('/', name: 'certificat_index', methods: ['GET'])]
    #[IsGranted('ROLE_CERTIFICAT_LIST')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $search = new CertificatSearch($request->query->all());
        $certificats = $em->getRepository(Certificat::class)->search($search);

        return $this->render('certificat/index.html.twig', [
            'certificats' => $certificats,
            'search' => [
                'page' => $search->page,
                'limit' => $search->limit,
                'count' => $certificats->count(),
            ],
        ]);
    }

    #[Route('/new', name: 'certificat_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CERTIFICAT_EDIT')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $certificat = new Certificat();
        $form = $this->createForm(CertificatType::class, $certificat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($certificat);
            $em->flush(); // pour avoir l'id
            $this->log('create', $certificat);
            $em->flush();

            return $this->redirectToRoute('certificat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('certificat/edit.html.twig', [
            'certificat' => $certificat,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'certificat_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CERTIFICAT_EDIT')]
    public function edit(Request $request, Certificat $certificat, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CertificatType::class, $certificat);
        $form->handleRequest($request);

        if ($request->isMethod("GET")) {
            $referer = $request->headers->get('referer');
            $form->get('_referer')->setData($referer);
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $this->log('update', $certificat);
            $em->flush();

            if ($form->get('_referer')->getData()) {
                return $this->redirect($form->get('_referer')->getData());
            }
            return $this->redirectToRoute('certificat_index', [], Response::HTTP_SEE_OTHER);
            //return $this->redirectToLastSearch(defaultRoute: 'certificat_index');
        }

        return $this->render('certificat/edit.html.twig', [
            'certificat' => $certificat,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'certificat_show', methods: ['GET'])]
    #[IsGranted('ROLE_CERTIFICAT_LIST')]
    public function show(Certificat $certificat): Response
    {
        return $this->render('certificat/show.html.twig', [
            'certificat' => $certificat,
        ]);
    }

    #[Route('/{id}', name: 'certificat_delete', methods: ['POST'])]
    #[IsGranted('ROLE_CERTIFICAT_EDIT')]
    public function delete(Certificat $certificat, EntityManagerInterface $em): Response
    {
        $em->remove($certificat);
        $this->log('delete', $certificat);
        $em->flush();

        return $this->redirectToRoute('certificat_index', [], Response::HTTP_SEE_OTHER);
    }

}
