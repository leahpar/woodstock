<?php

namespace App\Controller;

use App\Entity\Log;
use App\Search\LogSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LogController extends AbstractController
{
    #[Route('/logs', name: 'logs')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $search = new LogSearch($request->query->all());
        $logs = $em->getRepository(Log::class)->search($search);

        /** @var Log $log */
        foreach ($logs as $log) {
            if ($log->class) {
                $log->entity = $em->getRepository($log->class)->find($log->target);
            }
        }

        return $this->render('log/index.html.twig', [
            'logs' => $logs,
            'search' => [
                'page' => $search->page,
                'limit' => $search->limit,
                'count' => $logs->count(),
            ],
        ]);
    }
}
