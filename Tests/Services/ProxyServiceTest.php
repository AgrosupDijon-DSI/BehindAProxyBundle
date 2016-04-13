<?php

namespace Cnerta\BehindAProxyBundle\Tests\Services;

use Cnerta\BehindAProxyBundle\Services\ProxyService;

/**
 * @author Valérian Girard <valerian.girard@educagri.fr>
 * @group proxyService
 */
class ProxyServiceTest extends \PHPUnit_Framework_TestCase
{

    public function testStreamContextNotSet()
    {
        $proxyService = new ProxyService($this->defaultParameters());

        $this->assertFalse($proxyService->loadDefaultStreamContext());


        $param = array_merge($this->defaultParameters(), array("enabled" => true));

        $proxyService = new ProxyService($param);

        $this->assertFalse($proxyService->loadDefaultStreamContext());
    }

    public function testStreamContextIsSet()
    {
        $param = array_merge($this->defaultParameters(), array("enabled" => true,
            "load_default_stream_context" => true));

        $proxyService = new ProxyService($param);

        $this->assertTrue($proxyService->loadDefaultStreamContext());
    }

    public function testProxyForSoapClientEmpty()
    {
        $proxyService = new ProxyService($this->defaultParameters());

        $configs = array();
        $proxyService->setProxyForSoapClient($configs);

        $this->assertEmpty($configs);
    }

    public function dataProviderProxyForSoapClient()
    {
        return array(
            array('params' => array("enabled" => true, "host" => "127.0.0.1", "port" => "8080"),
                'configExpected' => array('proxy_host' => "127.0.0.1", 'proxy_port' => "8080")),
            array('params' => array("enabled" => true, "host" => "127.0.0.1", "port" => "8080", "login" => "regis.robert"),
                'configExpected' => array('proxy_host' => "127.0.0.1", 'proxy_port' => "8080", 'proxy_login' => "regis.robert")),
            array('params' => array("enabled" => true, "host" => "127.0.0.1", "port" => "8080", "password" => "thisissecret"),
                'configExpected' => array('proxy_host' => "127.0.0.1", 'proxy_port' => "8080", 'proxy_password' => "thisissecret")),
        );
    }

    /**
     * @dataProvider dataProviderProxyForSoapClient
     */
    public function testProxyForSoapClient($params, $configExpected)
    {
        $param = array_merge($this->defaultParameters(), $params);

        $proxyService = new ProxyService($param);

        $configs = array();
        $proxyService->setProxyForSoapClient($configs);

        $this->assertEquals($configExpected, $configs);
    }

    public function testProxyForSoapClientWithContext()
    {
        $param = array_merge($this->defaultParameters(), array("enabled" => true, "host" => "127.0.0.1", "port" => "8080"));
        $proxyService = new ProxyService($param);

        $configs = array();
        $proxyService->setProxyForSoapClient($configs, true);

        $this->assertArrayHasKey('stream_context', $configs);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testSetProxyForCURLWithFalseRessource()
    {
        $proxyService = new ProxyService($this->defaultParameters());
        $falseReource = null;
        $proxyService->setProxyForCURL($falseResource);
    }


    public function dataProviderGetStreamContext()
    {

        $auth = base64_encode('regis.robert:thisissecret');

        return array(
            array('params' => array(),
                "expected" => null),

            array('params' => array("enabled" => true, "host" => "127.0.0.1", "port" => "8080"),
                "expected" => array(
                    'http' => array(
                        'proxy' => 'tcp://127.0.0.1:8080',
                        'request_fulluri' => true,
                    ),
                    'https' => array(
                        'proxy' => 'tcp://127.0.0.1:8080',
                        'request_fulluri' => true,
                    )
                )),

            array('params' => array("enabled" => true, "host" => "127.0.0.2", "port" => "8080", "login" => "regis.robert", "password" => "thisissecret"),
                "expected" => array(
                    'http' => array(
                        'proxy' => 'tcp://127.0.0.2:8080',
                        'request_fulluri' => true,
                        'header' => "Proxy-Authorization: Basic $auth",
                    ),
                    'https' => array(
                        'proxy' => 'tcp://127.0.0.2:8080',
                        'request_fulluri' => true,
                        'header' => "Proxy-Authorization: Basic $auth")
                )
            ),

            array('params' => array(
                "enabled" => true, "host" => "", "port" => "", "login" => "", "password" => "",
                "http" => array(
                    "host_proxy" => "127.0.0.1",
                    "port_proxy" => "42",
                    "login_proxy" => "regis.robert",
                    "password_proxy" => "thisissecret",
                    "request_fulluri" => false
                )),
                "expected" => array(
                    'http' => array(
                        'proxy' => 'tcp://127.0.0.1:42',
                        'request_fulluri' => false,
                        'header' => "Proxy-Authorization: Basic $auth",
                    )
                    )
            )
        );

    }

    /**
     * @dataProvider dataProviderGetStreamContext
     */
    public function testGetStreamContext($params, $expected)
    {
        $param = array_merge($this->defaultParameters(), $params);

        $proxyService = new ProxyService($param);

        $context = $proxyService->getStreamContext();

        $this->assertEquals($expected, $context);
    }

    private function defaultParameters()
    {
        return array(
            "enabled" => false,
            "host" => null,
            "port" => null,
            "login" => null,
            "password" => null,
            "load_default_stream_context" => false,
        );
    }

}
