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

        if ($this->isGranted('ROLE_PLANNING_EDIT')) {
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

    #[Route('/new',       name: 'planning_new',  defaults: ['action' => 'create'])]
    #[Route('/{id}/edit', name: 'planning_edit', defaults: ['action' => 'update'])]
    public function new(
        string $action,
        Request $request,
        EntityManagerInterface $em,
        Intervention $intervention = null,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($action === 'create') {
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
        }

        /* Autoremplissage des heures planifiées suivant le jour et heures disponibles */
        $heuresDispo = match ((int)$intervention->date?->format('N')) {
            1, 2, 3 => 10,
            4 => 9,
            default => 0,
        };
        $interventions = $em->getRepository(Intervention::class)->findBy([
            'date' => $intervention->date,
            'poseur' => $intervention->poseur,
        ]);
        /** @var Intervention $i */
        foreach ($interventions as $i) {
            if ($i->id == $intervention->id) continue;
            $heuresDispo -= $i->heuresPlanifiees;
        }
        $intervention->heuresPlanifiees = $heuresDispo;
        /* END */

        // Poseurs sélectionnables
        if ($this->isGranted('ROLE_PLANNING_EDIT')) {
            $poseurs = $em->getRepository(User::class)->findBy(['disabled' => false]);
        }
        elseif ($user->chefEquipe) {
            $poseurs = $em->getRepository(User::class)->findBy(['disabled' => false, 'equipe' => $user->equipe]);
        }
        else {
            $poseurs = [$user];
        }

        $form = $this->createForm(InterventionType::class, $intervention, [
            'poseurs' => $poseurs,
            'max_heures_planifiees' => $heuresDispo,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($intervention);
            $em->flush(); // pour avoir l'id

            $this->log($action, $intervention, $intervention->toLogArray());
            $em->flush();

            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }

        return $this->render('planning/edit.html.twig', [
            'intervention' => $intervention,
            'form' => $form,
        ]);
    }

//    public function edit(Request $request, Intervention $intervention, EntityManagerInterface $em): Response
//    {
//        /** @var User $user */
//        $user = $this->getUser();
//
//        /* Autoremplissage des heures planifiées suivant le jour et heures disponibles */
//        $heuresDispo = match ((int)$intervention->date?->format('N')) {
//            1, 2, 3 => 10,
//            4 => 9,
//            default => 0,
//        };
//        $interventions = $em->getRepository(Intervention::class)->findBy([
//            'date' => $intervention->date,
//            'poseur' => $intervention->poseur,
//        ]);
//        /** @var Intervention $i */
//        foreach ($interventions as $i) {
//            if ($i->id == $intervention->id) continue;
//            $heuresDispo -= $i->heuresPlanifiees;
//        }
//        /* END */
//
//        $form = $this->createForm(InterventionType::class, $intervention, [
//            'is_chef_equipe' => $user->chefEquipe || $this->isGranted('ROLE_PLANNING_EDIT'),
//            'max_heures_planifiees' => $heuresDispo,
//        ]);
//
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//
//            $this->log('update', $intervention, $intervention->toLogArray());
//            $em->flush();
//
//            $referer = $request->headers->get('referer');
//            return $this->redirect($referer);
//        }
//
//        return $this->render('planning/edit.html.twig', [
//            'intervention' => $intervention,
//            'form' => $form->createView(),
//        ]);
//    }

//    #[Route('/{id}', name: 'planning_show', methods: ['GET'])]
//    public function show(Intervention $intervention): Response
//    {
//        return $this->render('planning/show.html.twig', [
//            'intervention' => $intervention,
//        ]);
//    }

    #[Route('/{id}/valider', name: 'planning_valider', methods: ['POST'])]
    public function valider(Request $request, Intervention $intervention, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->chefEquipe && !$this->isGranted('ROLE_PLANNING_EDIT')) {
            throw $this->createAccessDeniedException();
        }

        $valider = $request->request->getBoolean('valider');
        $intervention->valide = $valider;
        dump($intervention, $valider);

        $this->log('valider', $intervention, ['date' => $intervention->date->format('d/m/Y'), 'action' => $valider ? 'valider' : 'dévalider']);
        $em->flush();

        //$referer = $request->headers->get('referer');
        //return $this->redirect($referer);
        return new Response(null, Response::HTTP_NO_CONTENT);
    }


    #[Route('/{id}/supprimer', name: 'planning_delete', methods: ['POST'])]
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
