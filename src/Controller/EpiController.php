<?php

namespace App\Controller;

use App\Entity\Epi;
use App\Entity\User;
use App\Form\EpiType;
use App\Search\EpiSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/epi')]
class EpiController extends CommonController
{

    #[Route('/', name: 'epi_list')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $search = new EpiSearch($request->query->all());
        $epis = $em->getRepository(Epi::class)->search($search);

        return $this->render('epi/index.html.twig', [
            'epis' => $epis,
            'search' => [
                'page' => $search->page,
                'limit' => $search->limit,
                'count' => $epis->count(),
            ],
        ]);
    }

    #[Route('/new', name: 'epi_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER_EPI')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $epi = new Epi();
        $epi->user = $em->getRepository(User::class)->find($request->query->get('_u'));
        $epi->date = new \DateTime();

        $form = $this->createForm(EpiType::class, $epi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($epi);
            $em->flush();
            $this->log('epi', $epi->user, ['epi' => $epi->nom, 'date' => $epi->date->format('Y-m-d')]);
            $em->flush();

            return $this->redirectToRoute('user_show', ['id' => $epi->user->id], Response::HTTP_SEE_OTHER);
        }

        return $this->render('epi/edit.html.twig', [
            'epi' => $epi,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'epi_show', methods: ['GET', 'POST'])]
    public function show(Epi $epi)
    {
        return $this->redirectToRoute('user_show', ['id' => $epi->user->id], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/delete', name: 'epi_delete', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER_EPI')]
    public function delete(Request $request, Epi $epi, EntityManagerInterface $em): Response
    {
        $em->remove($epi);
        $this->log('epi_delete', $epi->user, ['epi' => $epi->nom, 'date' => $epi->date->format('Y-m-d')]);
        $em->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }


}
