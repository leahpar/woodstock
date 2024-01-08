<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SearchSubscriber implements EventSubscriberInterface
{

    public const SEARCH_ROUTES = [
        'search',
    ];

    public const NOT_SEARCH_ROUTES = [
        'reference_index',
        'chantier_index',
        'materiel_index',
        'user_index',
        'certificat_index',
    ];

    public static function getSubscribedEvents()
    {
        return [
            //KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (in_array($route, self::SEARCH_ROUTES)) {
            $request->getSession()->set('search', $request->query->get('search'));
//            array_map(
//                fn ($key) => $request->getSession()->set($key, $request->query->get($key)),
//                $request->query->all()
//            );
        } elseif (in_array($route, self::NOT_SEARCH_ROUTES)) {
            $request->getSession()->remove('search');
//            array_map(
//                fn ($key) => $request->getSession()->set($key, $request->query->get($key)),
//                $request->query->all()
//            );
        }
    }
}
