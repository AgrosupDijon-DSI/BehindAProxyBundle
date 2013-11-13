<?php

namespace Cnerta\BehindAProxyBundle\Tests\Listener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Cnerta\BehindAProxyBundle\Listener\ProxyListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Cnerta\BehindAProxyBundle\Services\ProxyService;

/**
 *
 */
class ProxyListenerTest extends \PHPUnit_Framework_TestCase
{


    public function testSubscribeEvents()
    {
        $expectedReturn  = array(KernelEvents::REQUEST => array('onKernelRequest', 8));

        $proxyListner = new ProxyListener(new ProxyService(array()));

        $this->assertEquals($expectedReturn, $proxyListner->getSubscribedEvents());
    }

    /**
     * Test if a non master request has no consequence
     *
     */
    public function testOnKernelRequestNoAction()
    {
        $proxyServiceMock = $this->getMockBuilder("Cnerta\BehindAProxyBundle\Services\ProxyService")
                ->disableOriginalConstructor()
                ->getMock();
        $proxyServiceMock->expects($this->never())
                ->method("loadDefaultStreamContext");

        $getResponseEventMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")
                ->disableOriginalConstructor()
                ->getMock();
        $getResponseEventMock->expects($this->once())
                ->method("getRequestType")
                ->will($this->returnValue(HttpKernelInterface::SUB_REQUEST));

        $proxyListner = new ProxyListener($proxyServiceMock);

        $proxyListner->onKernelRequest($getResponseEventMock);
    }

    /**
     * Test if a master request has consequence
     */
    public function testOnKernelRequestHasAction()
    {
        $proxyServiceMock = $this->getMockBuilder("Cnerta\BehindAProxyBundle\Services\ProxyService")
                ->disableOriginalConstructor()
                ->getMock();
        $proxyServiceMock->expects($this->once())
                ->method("loadDefaultStreamContext");

        $getResponseEventMock = $this->getMockBuilder("Symfony\Component\HttpKernel\Event\GetResponseEvent")
                ->disableOriginalConstructor()
                ->getMock();
        $getResponseEventMock->expects($this->once())
                ->method("getRequestType")
                ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $proxyListner = new ProxyListener($proxyServiceMock);

        $proxyListner->onKernelRequest($getResponseEventMock);
    }

}
