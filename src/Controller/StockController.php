<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Form\StockEntreeType;
use App\Form\StockSortieType;
use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/stock')]
class StockController extends AbstractController
{

    #[Route('/{type}', name: 'reference_stock', requirements: ['type' => 'entree|sortie'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_REFERENCE_STOCK')]
    public function stock(Request $request, StockRepository $stockRepository, string $type): Response
    {
        $stock = new Stock();
        $stock->type = $type;

        $form = match ($type) {
            'entree' => $this->createForm(StockEntreeType::class, $stock),
            'sortie' => $this->createForm(StockSortieType::class, $stock),
        };

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stockRepository->save($stock, true);

            return $this->redirectToRoute('reference_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reference/stock.html.twig', [
            'stock' => $stock,
            'form' => $form->createView(),
        ]);
    }



}
