<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Search\NotificationSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotifController extends CommonController
{
    #[Route('/notifications', name: 'notifs')]
    public function index(Request $request, EntityManagerInterface $em)
    {
        /** @var User $user */
        $user = $this->getUser();

        $search = new NotificationSearch($request->query->all());
        $search->tri = 'date';
        $search->order = 'DESC';

        // Filtre par rôle (admin peut voir toutes les notifs)
        if (!$this->isGranted('ROLE_ADMIN')) {
            $search->roles = $user->getRoles();
        }

        // TODO: add pagination & search
        $search->limit = 0;

        $notifs = $em->getRepository(Notification::class)->search($search);
        $notifs = $notifs->getIterator();

        /** @var Notification $notif */
        foreach ($notifs as $notif) {
            if ($notif->class) {
                $notif->entity = $em->getRepository($notif->class)->find($notif->target);
            }
        }

        return $this->render('notification/index.html.twig', [
            'notifs' => $notifs,
            "page" => $search->page,
            "limit" => $search->limit,
            "count" => $notifs->count(),
        ]);
    }

    #[Route('/notifications/{id}/traiter', name: 'notif_traiter', methods: ['POST'])]
    public function traiter(Request $request, EntityManagerInterface $em, Notification $notif)
    {
        /** @var User $user */
        $user = $this->getUser();

        $notif->dateTraite = new \DateTime();
        $notif->user = $user;

        $this->log('notification', null, ['notif' => $notif->message]);
        $em->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

    #[Route('/notifications/{id}/ping', name: 'notif_ping', methods: ['POST'])]
    public function ping(EntityManagerInterface $em, Notification $notif)
    {
        // ajax only
        /** @var User $user */
        $user = $this->getUser();
        $user->removePing($notif);
        $em->flush();
        return new Response();
    }


}
