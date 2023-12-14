<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\User;
use App\Form\InterventionType;
use App\Search\InterventionSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/planning')]
class InterventionController extends CommonController
{
    #[Route('/', name: 'planning_index', methods: ['GET'])]
    #[IsGranted('ROLE_PLANNING_LIST')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        if (!$request->query->has('semaine')) {
            return $this->redirectToRoute('planning_index', ['semaine' => date('W')]);
        }

        $search = new InterventionSearch([
            'semaine'   => date('W'),
            'page'      => 1,
            'limit'     => 0,
            ...$request->query->all(),
        ]);
        $interventions = $em->getRepository(Intervention::class)->search($search);

        $form = $this->createForm(InterventionType::class);

        return $this->render('planning/index.html.twig', [
            'interventions' => $interventions->getIterator(),
            'dateStart' => $search->dateStart,
            'dateEnd' => $search->dateEnd,
            'semaine' => $search->semaine,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'planning_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PLANNING_EDIT')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $intervention = new Intervention();

        if ($request->isMethod('GET')) {
            $intervention->date = new \DateTime($request->query->get('date'));
            $intervention->heures = $request->query->getInt('heures');
            $poseur = $em->getRepository(User::class)->find($request->query->getInt('poseur'));
            $intervention->poseur = $poseur;
        }

        $form = $this->createForm(InterventionType::class, $intervention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($intervention);
            $em->flush(); // pour avoir l'id
            $this->log('create', $intervention, $intervention->toLogArray());
            $em->flush();

            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }

        return $this->render('planning/edit.html.twig', [
            'intervention' => $intervention,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'planning_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PLANNING_EDIT')]
    public function edit(Request $request, Intervention $intervention, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(InterventionType::class, $intervention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->log('update', $intervention, $intervention->toLogArray());
            $em->flush();

            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }

        return $this->render('planning/edit.html.twig', [
            'intervention' => $intervention,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'planning_show', methods: ['GET'])]
    #[IsGranted('ROLE_PLANNING_LIST')]
    public function show(Intervention $intervention): Response
    {
        return $this->render('planning/show.html.twig', [
            'intervention' => $intervention,
        ]);
    }

    #[Route('/{id}', name: 'planning_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PLANNING_EDIT')]
    public function delete(Intervention $intervention, EntityManagerInterface $em): Response
    {
        $em->remove($intervention);
        $this->log('delete', $intervention, $intervention->toLogArray());
        $em->flush();

        return $this->redirectToRoute('planning_index', [], Response::HTTP_SEE_OTHER);
    }

}
