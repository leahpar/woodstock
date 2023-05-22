<?php

namespace App\Logger;

use App\Entity\Log;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class LoggerService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ) {}

    public function log(string $action, LoggableEntity $entity, array $data = []): void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $log = new Log($action, $user, $entity);
        $log->user = $user;
        $log->data = $data;
        $this->em->persist($log);
    }

}
