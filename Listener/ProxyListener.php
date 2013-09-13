<?php

namespace Cnerta\BehindAProxyBundle\Listener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Cnerta\BehindAProxyBundle\Services\ProxyService;

/**
 *
 */
class ProxyListener implements EventSubscriberInterface
{
    /**
     * @var Cnerta\BehindAProxyBundle\Services\ProxyService
     */
    protected $proxyService;
 
    function __construct(ProxyService $proxyService)
    {
        $this->proxyService = $proxyService;
    }

        /**
     * Handles security.
     *
     * @param GetResponseEvent $event An GetResponseEvent instance
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST == $event->getRequestType()) {
            $this->proxyService->streamContextSetDefault();
        }
    }

    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => array('onKernelRequest', 8));
    }
}
