<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Stock;
use App\Entity\User;
use App\Form\PanierType;
use App\Service\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/paniers')]
class PanierController extends CommonController
{

    #[Route('/{type}', name: 'panier_new', requirements: ['type' => 'retour|entree|sortie'])]
    #[IsGranted('ROLE_REFERENCE_STOCK')]
    public function new(EntityManagerInterface $em, string $type, PanierService $panierService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $panier = $panierService->getPanierBrouillon($user, $type);
        $em->persist($panier);
        $em->flush();

        return $this->redirectToRoute('panier_edit', ['id' => $panier->id]);
    }

    #[Route('/{id}', name: 'panier_edit')]
    #[IsGranted('ROLE_REFERENCE_STOCK')]
    public function edit(Request $request, Panier $panier, EntityManagerInterface $em): Response
    {
        if ($panier->brouillon === false && !$this->isGranted('ROLE_ADMIN')) {
            return $this->forward('App\Controller\PanierController::show', [
                'panier' => $panier,
            ]);
        }

        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $stock = $panier->stock;
            $stock->type = $panier->type;
            $stock->prix = $stock->reference->prix;
            $panier->addStock($stock);
            $em->persist($stock);

            $reference = $stock->reference;
            $em->flush();

            if ($reference->getQuantite() < 0) {
                $this->addFlash("error", "Stock négatif pour {$reference} !");
            }
            elseif ($reference->getQuantite() < $reference->seuil) {
                $this->addFlash("warning", "Stock bas pour {$reference} !");
            }


            return $this->redirectToRoute('panier_edit', ['id' => $panier->id]);
        }

        return $this->render('panier/edit.html.twig', [
            'panier' => $panier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'panier_show', methods: ['GET'])]
    public function show(Panier $panier): Response
    {
        return $this->render('panier/show.html.twig', [
            'panier' => $panier,
        ]);
    }

    #[Route('/{id}/del', name: 'panier_del', methods: ['DELETE'])]
    public function del(Request $request, EntityManagerInterface $em, Panier $panier)
    {
        $em->remove($panier);
        $em->flush();

        if ($panier->brouillon) {
            $this->addFlash("success", "Le panier a été annulé");
            return $this->redirectToRoute('dashboard');
        }
        else {
            // TODO: check rôle
            $this->addFlash("success", "La sortie de stock a été supprimée");
            $referer = $request->headers->get('referer', '/');
            return $this->redirect($referer);
        }
    }

    #[Route('/{id}/del/{stock}', name: 'panier_stock_del')]
    public function delStock(EntityManagerInterface $em, Panier $panier, Stock $stock)
    {
        $em->remove($stock);
        $em->flush();

        return $this->redirectToRoute('panier_edit', ['id' => $panier->id]);
    }

    #[Route('/{id}/save', name: 'panier_save')]
    public function save(EntityManagerInterface $em, Panier $panier)
    {
        // Notif stock bas
        $refs = [];
        foreach ($panier->stocks as $stock) {
            $reference = $stock->reference;

            // On ne traite qu'une seule fois chaque référence
            if (in_array($reference, $refs)) continue;

            if ($reference->getQuantite() < 0) {
                $this->notif("ROLE_REFERENCE_EDIT", $reference, "Stock négatif pour {$reference} !");
            }
            elseif ($reference->getQuantite() < $reference->seuil) {
                $this->notif("ROLE_REFERENCE_EDIT", $reference, "Stock bas pour {$reference} !");
            }

            $refs[] = $reference;
        }

        $panier->brouillon = false;
        //$em->flush(); // pour avoir l'id // Pas besoin, le panier existe déjà
        $this->log('create', $panier);
        $em->flush();

        $this->addFlash("success", "Le panier a été enregistré");
        return $this->redirectToRoute('dashboard');
    }


}
