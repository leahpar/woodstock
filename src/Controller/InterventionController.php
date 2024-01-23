<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\User;
use App\Form\InterventionType;
use App\Search\InterventionSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/planning')]
class InterventionController extends CommonController
{
    #[Route('/', name: 'planning_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        if (!$request->query->has('semaine') && !$request->query->has('mois')) {
            return $this->redirectToRoute('planning_index', ['semaine' => date('W')]);
        }

        $search = new InterventionSearch([
            'annee'     => $request->query->getInt('annee', date('Y')),
            'semaine'   => (int)$request->query->get('semaine'),
            'mois'      => (int)$request->query->get('mois'),
            'page'      => 1,
            'limit'     => 0,
            ...$request->query->all(),
        ]);

        /** @var User $user */
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            $search->equipe = null;
            $search->poseur = null;
        }
        elseif ($user->chefEquipe) {
            $search->equipe = $user->equipe;
        }
        else {
            $search->poseur = $user;
        }

        $interventions = $em->getRepository(Intervention::class)->search($search);

        $form = $this->createForm(InterventionType::class);

        return $this->render('planning/index.html.twig', [
            'interventions' => $interventions->getIterator(),
            'search' => $search,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'planning_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->chefEquipe && !$this->isGranted('ROLE_PLANNING_EDIT')) {
            throw $this->createAccessDeniedException();
        }

        $intervention = new Intervention();

        if ($request->isMethod('GET')) {
            $intervention->date = new \DateTime($request->query->get('date'));
            $intervention->heuresPlanifiees = $request->query->getInt('heures');
            $poseur = $em->getRepository(User::class)->find($request->query->getInt('poseur'));
            $intervention->poseur = $poseur;
        }

        // Config manuelle
        $intervention->tauxHoraire = 50;
        if ($intervention->chantier) {
            $intervention->tauxHoraire = $intervention->chantier->tauxHoraire;
        }
        $intervention->heuresPlanifiees = match ((int)$intervention->date?->format('N')) {
            1, 2, 3 => 10,
            4 => 9,
            default => 0,
        };

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
    public function edit(Request $request, Intervention $intervention, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(InterventionType::class, $intervention, [
            'is_chef_equipe' => $user->chefEquipe || $this->isGranted('ROLE_PLANNING_EDIT'),
        ]);

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

//    #[Route('/{id}', name: 'planning_show', methods: ['GET'])]
//    public function show(Intervention $intervention): Response
//    {
//        return $this->render('planning/show.html.twig', [
//            'intervention' => $intervention,
//        ]);
//    }

    #[Route('/valider', name: 'planning_valider', methods: ['POST'])]
    public function valider(Request $request, EntityManagerInterface $em): Response
    {
        if (!$request->request->has('date')) {
            throw $this->createNotFoundException();
        }

        /** @var User $user */
        $user = $this->getUser();
        if (!$user->chefEquipe && !$this->isGranted('ROLE_PLANNING_EDIT')) {
            throw $this->createAccessDeniedException();
        }

        $date = new \DateTime($request->request->get('date'));
        $valider = $request->request->getBoolean('valider');
        $interventions = $em->getRepository(Intervention::class)->findBy([
            'date' => $date,
        ]);
        foreach ($interventions as $intervention) {
            $intervention->valide = $valider;
        }

        $this->log('valider', null, ['date' => $date->format('d/m/Y'), 'action' => $valider ? 'valider' : 'dévalider']);
        $em->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
        //return $this->redirectToRoute('planning_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}', name: 'planning_delete', methods: ['POST'])]
    public function delete(Request $request, Intervention $intervention, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->chefEquipe && !$this->isGranted('ROLE_PLANNING_EDIT')) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($intervention);
        $this->log('delete', $intervention, $intervention->toLogArray());
        $em->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
        //return $this->redirectToRoute('planning_index', [], Response::HTTP_SEE_OTHER);
    }



}
