<?php

namespace App\Controller;

use App\Entity\Materiel;
use App\Entity\Pret;
use App\Form\MaterielType;
use App\Form\PretType;
use App\Repository\MaterielRepository;
use App\Search\MaterielSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/materiel')]
class MaterielController extends CommonController
{
    #[Route('/', name: 'materiel_index', methods: ['GET'])]
    #[IsGranted('ROLE_MATERIEL_LIST')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $search = new MaterielSearch($request->query->all());
        $materiels = $em->getRepository(Materiel::class)->search($search);

        return $this->render('materiel/index.html.twig', [
            'materiels' => $materiels,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'materiel_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MATERIEL_EDIT')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $materiel = new Materiel();
        $form = $this->createForm(MaterielType::class, $materiel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($materiel);
            $em->flush(); // pour avoir l'id
            $this->log('create', $materiel);
            $em->flush();

            return $this->redirectToRoute('materiel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('materiel/edit.html.twig', [
            'materiel' => $materiel,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'materiel_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MATERIEL_EDIT')]
    public function edit(Request $request, Materiel $materiel, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MaterielType::class, $materiel);
        $form->handleRequest($request);

        if ($request->isMethod("GET")) {
            $referer = $request->headers->get('referer');
            $form->get('_referer')->setData($referer);
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $this->log('update', $materiel);
            $em->flush();

            if ($form->get('_referer')->getData()) {
                return $this->redirect($form->get('_referer')->getData());
            }
            return $this->redirectToRoute('materiel_index', [], Response::HTTP_SEE_OTHER);
            //return $this->redirectToLastSearch(defaultRoute: 'materiel_index');
        }

        return $this->render('materiel/edit.html.twig', [
            'materiel' => $materiel,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'materiel_show', methods: ['GET'])]
    #[IsGranted('ROLE_MATERIEL_LIST')]
    public function show(Materiel $materiel): Response
    {
        return $this->render('materiel/show.html.twig', [
            'materiel' => $materiel,
        ]);
    }

    #[Route('/{id}', name: 'materiel_delete', methods: ['POST'])]
    #[IsGranted('ROLE_MATERIEL_EDIT')]
    public function delete(Materiel $materiel, EntityManagerInterface $em): Response
    {
        $em->remove($materiel);
        $this->log('delete', $materiel);
        $em->flush();

        return $this->redirectToRoute('materiel_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/pret', name: 'materiel_pret')]
    public function pret(Request $request, Materiel $materiel, EntityManagerInterface $em): Response
    {
        $rendu = $request->query->getBoolean('rendu');
        $pretEnCours = $materiel->getPretEnCours();

        if ($rendu && !$pretEnCours) {
            // Rendu sans prêt en cours => KO
            $this->addFlash('warning', 'Aucun prêt en cours pour ce matériel');
            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }
        elseif (!$rendu && $pretEnCours) {
            // Nouveau prêt avec prêt déjà en cours => KO
            $this->addFlash('warning', 'Ce matériel est déjà prêté');
            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }

        $pret = $pretEnCours ?? new Pret(materiel: $materiel);

        if ($rendu) {
            // Rendu => date de retour = maintenant par défaut
            $pret->dateRetour = new \DateTime();
        }

        $form = $this->createForm(PretType::class, $pret, ['rendu' => $rendu]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($pret);
            if ($rendu) {
                $this->log('rendu', $materiel, [
                    'date' => $pret->dateRetour->format('Y-m-d'),
                    'emprunteur' => $pret->user->__toString(),
                ]);
            }
            else {
                $this->log('pret', $materiel, [
                    'date' => $pret->datePret->format('Y-m-d'),
                    'emprunteur' => $pret->user->__toString(),
                ]);
            }
            $em->flush();

            return $this->redirectToRoute('materiel_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('materiel/pret.html.twig', [
            'materiel' => $materiel,
            'form' => $form->createView(),
            'rendu' => $rendu,
        ]);
    }

}
