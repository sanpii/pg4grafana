<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class CorsResponseListener
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $headers = $event->getResponse()->headers;
        $headers->set('Access-Control-Allow-Origin', '*');
        $headers->set('Access-Control-Request-Method', '*');
        $headers->set('Access-Control-Allow-Methods', 'OPTIONS, GET');
        $headers->set('Access-Control-Allow-Headers', 'accept, content-type');
    }
}
