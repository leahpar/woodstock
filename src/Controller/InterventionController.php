<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\User;
use App\Form\InterventionHeuresType;
use App\Form\InterventionType;
use App\Search\InterventionSearch;
use App\Service\InterventionService;
use App\Service\ParamService;
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
    public function edit(
        string $action,
        Request $request,
        EntityManagerInterface $em,
        ParamService $paramService,
        InterventionService $interventionService,
        Intervention $intervention = null,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($action === 'create') {
            $intervention = new Intervention();
            $intervention->auteur = $user;

            if ($request->isMethod('GET')) {
                // Infos pour préremplissage du formulaire
                $interventionService->preremplissage($intervention, $request->query->all());
            }
        }

//        if ($intervention->hasEnfantValide()) {
//            return $this->render('planning/edit_ko.html.twig', [
//                "message" => "L'intervention (ou une intervention liée) est validée, impossible de la modifier",
//            ]);
//        }

        if ($intervention->valide) {
            return $this->render('planning/edit_ko.html.twig', [
                "message" => "L'intervention est validée, impossible de la modifier",
            ]);
        }

        // Poseur édite une intervention pas à lui => readonly
        $readonly = false;
        if (!$this->isGranted('ROLE_PLANNING_EDIT')
            && !$user->chefEquipe
            && $intervention->auteur->id != $user->id) {
            $readonly = true;
        }

        $form = $this->createForm(InterventionType::class, $intervention, ['readonly' => $readonly]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($action === 'create') {
                // Taux horaire
                $intervention->tauxHoraire = $paramService->get('taux_horaire_' . $intervention->date->format('Y')) ?? 50;

                // Poseurs à dupliquer
                $ids = $request->request->all('dupliquer_poseurs');
                if (count($ids) == 0) {
                    // Poseur de l'intervention par défaut
                    $poseurs = [$intervention->poseur];
                }
                elseif (in_array('all', $ids)) {
                    // Tout le monde non masqué planning et non désactivé
                    $poseurs = $em->getRepository(User::class)->findAllPlanning();
                }
                elseif (in_array('equipe', $ids)) {
                    // Equipe
                    $poseurs = $em->getRepository(User::class)->findBy([
                        'equipe' => $intervention->poseur->equipe,
                        'disabled' => false,
                        'masquerPlanning' => false,
                    ]);
                }
                else {
                    // Poseurs sélectionnés
                    $poseurs = $em->getRepository(User::class)->findBy(['id' => $ids]);
                }

                // Jours à dupliquer
                $jours = $request->request->all('dupliquer_jours');
                if (count($jours) == 0) {
                    // Jour de l'intervention par défaut
                    $jours = [$intervention->date->format('N')];
                }

                // Go dupliquer !
                $interventions = $interventionService->propagerCreation($intervention, $jours, $poseurs);
            }
            elseif ($intervention->parent && $request->request->getBoolean('all')) {
                $interventions = $interventionService->propagerModification($intervention);
            }
            else {
                $interventionService->detach($intervention);
                $interventions = [$intervention];
            }

            $em->flush(); // pour avoir les ids

            /** @var Intervention $i */
            foreach ($interventions as $i) {
                $this->log($action, $i, [...$i->toLogArray(), 'multiple' => 'oui']);
            }
            $em->flush();


            $s = count($interventions) > 1 ? 's' : '';
            $msg = count($interventions)." intervention$s ";
            $msg .= ($action == 'create') ? "ajoutée$s" : "mise$s à jour";
            $this->addFlash('success', $msg);

            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }

        //dump($intervention);
        $poseursEquipe = $em->getRepository(User::class)->findBy(['equipe' => $intervention->poseur->equipe]);

        return $this->render('planning/edit.html.twig', [
            'intervention' => $intervention,
            'poseursEquipe' => $poseursEquipe??[],
            'form' => $form,
            'formHeures' => $action == 'create' ? null : $this->createForm(InterventionHeuresType::class, $intervention),
        ]);
    }

    #[Route('/{id}/heures', name: 'planning_edit_heures')]
    public function editHeures(
        Request $request,
        EntityManagerInterface $em,
        Intervention $intervention,
    ): Response
    {

        if ($intervention->valide) {
            return $this->render('planning/edit_ko.html.twig', [
                "message" => "L'intervention est validée, impossible de la modifier",
            ]);
        }

        $form = $this->createForm(InterventionHeuresType::class, $intervention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush(); // pour avoir les ids

            $this->log("heures", $intervention, ['heures' => $intervention->heuresPassees . 'h']);
            $em->flush();

            $this->addFlash('success', "Heures enregistrées");

            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }

        return $this->render('planning/edit_ko.html.twig', [
            "message" => "xxxxxxxxxx",
        ]);
    }

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

        $this->log('valider', $intervention, ['date' => $intervention->date->format('d/m/Y'), 'action' => $valider ? 'valider' : 'dévalider']);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/supprimer', name: 'planning_delete', methods: ['POST'])]
    public function delete(Request $request, Intervention $intervention, EntityManagerInterface $em, InterventionService $interventionService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // TODO: InterventionVoter
        $canDelete = $user->chefEquipe || $this->isGranted('ROLE_PLANNING_EDIT') || $user === $intervention->auteur;
        if (!$canDelete || $intervention->valide) {
            throw $this->createAccessDeniedException();
        }

        // Détache l'intervention de son groupe
        $interventionService->detach($intervention);
        $em->remove($intervention);

        $this->log('delete', $intervention, $intervention->toLogArray());
        $em->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

    /**
     * Supprime toutes les interventions liées
     */
    #[Route('/{id}/supprimer-all', name: 'planning_delete_all', methods: ['POST'])]
    public function deleteAll(Request $request, Intervention $intervention, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // TODO: InterventionVoter
        $canDelete = $user->chefEquipe || $this->isGranted('ROLE_PLANNING_EDIT') || $user === $intervention->auteur;
        if (!$canDelete) {
            throw $this->createAccessDeniedException();
        }

        $parent = $intervention->parent ?? $intervention;
        $enfants = $intervention->enfants??[$intervention];
        /** @var Intervention $enfant */
        foreach ($enfants as $enfant) {
            if ($enfant->valide) throw $this->createAccessDeniedException('Impossible de supprimer une intervention validée');
            $this->log('delete', $enfant, [...$enfant->toLogArray(), 'multiple' => 'oui']);
        }

        $em->remove($parent); // NB: delete cascade pour les enfants

        $em->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

    /**
     * Supprime toutes les interventions (non validées) de la semaine du poseur
     */
    #[Route('/{id}/supprimer-poseur', name: 'planning_delete_semaine', methods: ['POST'])]
    public function deletePoseur(Request $request, User $poseur, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // TODO: InterventionVoter
        $canDelete = $user->chefEquipe || $this->isGranted('ROLE_PLANNING_EDIT');
        if (!$canDelete) {
            throw $this->createAccessDeniedException();
        }

        $date = $request->request->get('date');
        $interventions = $em->getRepository(Intervention::class)->findByPoseurSemaine($poseur, $date);

        /** @var Intervention $intervention */
        $cpt = 0;
        foreach ($interventions as $intervention) {

            if ($intervention->valide) {
                // On la détache, mais on ne la supprime pas
                $intervention->parent = null;
                continue;
            }

            if ($intervention->activite === 'ferie') {
                continue;
            }

            $this->log('delete', $intervention, [...$intervention->toLogArray(), 'semaine' => 'oui']);
            $em->remove($intervention);
            $cpt++;
        }
        $em->flush();

        if ($cpt == 0) {
            $this->addFlash('warning', "Aucune intervention à supprimer");
        }
        else {
            $s = $cpt > 1 ? 's' : '';
            $this->addFlash('success', "$cpt intervention$s supprimée$s");
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

}
