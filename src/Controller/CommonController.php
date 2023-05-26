<?php

namespace App\Controller;

use App\Logger\LoggableEntity;
use App\Logger\LoggerService;
use App\Notifier\NotifierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommonController extends AbstractController
{

    public function __construct(
        protected LoggerService $log,
        protected NotifierService $notifier,
    ) {}

    public function log(string $action, ?LoggableEntity $entity, array $data = []): void
    {
        $this->log->log($action, $entity, $data);
    }

    public function notif(string $role, ?LoggableEntity $entity, string $message): void
    {
        $this->notifier->notif($role, $entity, $message);
    }

}
