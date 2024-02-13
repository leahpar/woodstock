<?php

namespace App\Controller;

use App\Entity\Param;
use App\Service\ParamService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ParamController extends CommonController
{

    #[Route('/parametrage', name: 'parametrage')]
    #[IsGranted("ROLE_ADMIN")]
    public function param(Request $request, EntityManagerInterface $em, ParamService $paramService)
    {

        if ($request->isMethod("POST")) {
            foreach ($request->request->all() as $nom => $valeur) {
                $paramService->saveParam($nom, $valeur);
            }
            $em->flush();
            $this->addFlash('success', 'Paramètres enregistrés');
            return $this->redirectToRoute('parametrage');
        }

        $params = $em->getRepository(Param::class)->findAll();
        $params = array_combine(
            array_map(fn($p) => $p->nom, $params),
            array_map(fn($p) => $p->valeur, $params),
        );

        return $this->render('parametrage/edit.html.twig', [
            'params' => $params,
        ]);
    }


}
