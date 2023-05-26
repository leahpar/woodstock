<?php

namespace App\Notifier;

use App\Entity\Notification;
use App\Entity\User;
use App\Logger\LoggableEntity;
use App\Search\NotificationSearch;
use Doctrine\ORM\EntityManagerInterface;

class NotifierService
{

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function notif(string $role, LoggableEntity $entity, string $message): void
    {
        $notif = new Notification($entity);
        $notif->role = $role;
        $notif->message = $message;
        $this->em->persist($notif);

        $this->pings($notif);
    }

    private function pings(Notification $notification): void
    {
        $users = $this->em->getRepository(User::class)->findByHasRole($notification->role);
        foreach ($users as $user) {
            $notification->addPing($user);
        }
    }

}
