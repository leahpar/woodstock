<?php

namespace App\Service;

use App\Entity\Panier;
use App\Entity\Reference;
use App\Entity\Stock;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PanierService
{

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function getPanierBrouillon(User $user, string $type): Panier
    {
        return $this->em->getRepository(Panier::class)->findOneBy([
            'user' => $user,
            'type' => $type,
            'brouillon' => true,
        ]) ?? $this->newPanier($user, $type);
    }

    private function newPanier(User $user, string $type, ?bool $brouillon = true): Panier
    {
        $panier = new Panier();
        $panier->user = $user;
        $panier->type = $type;
        $panier->brouillon = $brouillon;
        return $panier;
    }

    public function stockRegulReference(Reference $reference, int $quantite, User $user): void
    {
        $stock = new Stock();
        $stock->reference = $reference;
        $stock->type = $quantite > 0 ? Stock::TYPE_ENTREE : Stock::TYPE_SORTIE;
        $stock->prix = $reference->prix;
        $stock->quantite = abs($quantite);

        $reference->addStock($stock);

        $panier = $this->newPanier($user, $quantite > 0 ? Stock::TYPE_ENTREE : Stock::TYPE_SORTIE, brouillon: false);
        $panier->addStock($stock);

        $this->em->persist($panier);
        $this->em->persist($stock);
    }
}
