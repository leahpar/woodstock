<?php

namespace App\Controller;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LogController extends AbstractController
{
    #[Route('/logs', name: 'logs')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(EntityManagerInterface $em)
    {
        // TODO: add pagination & search
        $logs = $em->getRepository(Log::class)->findBy(
            [],
            ['date' => 'DESC'],
        );

        /** @var Log $log */
        foreach ($logs as $log) {
            $log->entity = $em->getRepository($log->class)->find($log->target);
        }

        return $this->render('log/index.html.twig', [
            'logs' => $logs,
        ]);
    }
}
