<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Stock;
use App\Entity\User;
use App\Form\PanierType;
use App\Form\StockType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/paniers')]
class PanierController extends AbstractController
{

    #[Route('/{type}', name: 'panier_new', requirements: ['type' => 'entree|sortie'])]
    #[IsGranted('ROLE_REFERENCE_STOCK')]
    public function new(EntityManagerInterface $em, string $type)
    {
        /** @var User $user */
        $user = $this->getUser();

        $panier = $em->getRepository(Panier::class)->findOneBy([
            'user' => $user,
            'type' => $type,
            'brouillon' => true,
        ]);

        if (!$panier) {
            $panier = new Panier();
            $panier->user = $user;
            $panier->type = $type;
            $panier->brouillon = true;
            $em->persist($panier);
            $em->flush();
        }

        return $this->redirectToRoute('panier_edit', ['id' => $panier->id]);
    }

    #[Route('/{id}', name: 'panier_edit')]
    #[IsGranted('ROLE_REFERENCE_STOCK')]
    public function edit(Request $request, Panier $panier, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $stock = $panier->stock;
            $stock->type = $panier->type;
            $stock->prix = $stock->reference->prix;
            $panier->addStock($stock);
            $em->persist($stock);
            $em->flush();

            $reference = $stock->reference;
            if ($reference->getQuantite() < 0) {
                $this->addFlash("error", "Stock négatif pour {$reference->nom} !");
            }
            elseif ($reference->getQuantite() < $reference->seuil) {
                $this->addFlash("warning", "Stock bas pour {$reference->nom}");
            }

            return $this->redirectToRoute('panier_edit', ['id' => $panier->id]);
        }

        return $this->render('panier/edit.html.twig', [
            'panier' => $panier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/del', name: 'panier_del', methods: ['DELETE'])]
    public function del(EntityManagerInterface $em, Panier $panier)
    {
        $em->remove($panier);
        $em->flush();

        $this->addFlash("success", "Le panier a été annulé");
        return $this->redirectToRoute('dashboard');
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
        $panier->brouillon = false;
        $em->flush();

        $this->addFlash("success", "Le panier a été enregistré");
        return $this->redirectToRoute('dashboard');
    }


}
