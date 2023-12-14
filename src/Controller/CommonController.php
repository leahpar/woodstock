<?php

namespace App\Controller;

use App\Logger\LoggableEntity;
use App\Logger\LoggerService;
use App\Notifier\NotifierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class CommonController extends AbstractController
{

    public function __construct(
        protected readonly LoggerService   $log,
        protected readonly NotifierService $notifier,
        private   readonly RequestStack    $requestStack,
    ) {}

    public function log(string $action, ?LoggableEntity $entity, array $data = []): void
    {
        $this->log->log($action, $entity, $data);
    }

    public function notif(string $role, ?LoggableEntity $entity, string $message): void
    {
        $this->notifier->notif($role, $entity, $message);
    }

    public function redirectToLastSearch(?string $defaultRoute = null): Response
    {
        $search = $this->requestStack->getSession()->get('search');
        $route = $defaultRoute ?? 'dashboard';
        $params = [];
        if ($search) {
            $route = 'search';
            $params = ['search' => $search];
        }
        return $this->redirectToRoute($route, $params, Response::HTTP_SEE_OTHER);
    }

}
