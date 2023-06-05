<?php

namespace App\Controller;

use App\Entity\Reference;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/test')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class TestController extends AbstractController
{
    #[Route('/updateCategoriesComptaAnalytique', name: 'updateCategoriesComptaAnalytique')]
    public function updateCategoriesComptaComptes(EntityManagerInterface $em)
    {
        $codes = [
            // BOIS
            "CHARPENTE" => 60120000,
            "OSSATURE" => 60120000,
            "CHARPENTE DOUGLAS" => 60120000,
            "LAMELLES"=> 60120000,
            // Quincaillerie
            "EQUERRES" => 60130000,
            "FIXATIONS" => 60130000,
            "BOULONNERIE CHARPENTE" => 60130000,
            "ROULEAUX" => 60130000,
            "SABOTS" => 60130000,
            "ETRIERS" => 60130000,
            "CARTOUCHE" => 60130000,
            // Autres
            "ECHAFAUDAGE" => 60150000,
            "PANNEAUX" => 60150000,
        ];
        $references = $em->getRepository(Reference::class)->findAll();
        /** @var Reference $ref */
        foreach ($references as $ref) {
            $ref->codeComptaCompte = $codes[$ref->categorie] ?? 60150000;
        }
        $em->flush();
        return new Response('ok');
    }

    #[Route('/updateReferenceConditionnement', name: 'updateReferenceConditionnement')]
    public function updateReferenceConditionnement(EntityManagerInterface $em)
    {
        $references = $em->getRepository(Reference::class)->findAll();
        /** @var Reference $ref */
        foreach ($references as $ref) {
            $ref->conditionnement = match ($ref->conditionnement) {
                "pièce", "pc", "Cartouche", => "Unité",
                "1ML", "1 ML", "ML", "ml" => "ML",
                "Boite", "Boîte" => "Boîte",
            };
        }
        $em->flush();
        return new Response('ok');
    }
}
