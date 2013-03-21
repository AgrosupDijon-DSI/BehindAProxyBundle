<?php

namespace Cnerta\ProxyBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Valérian Girard <valerian.girard@educagri.fr>
 */
class ProxyService
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Set proxy parameters for a SoapClient connection
     *
     * @param array $configs
     */
    public function setProxyForSoapClient(array &$configs)
    {
        if ($this->container->hasParameter("cnerta_proxy.enabled")) {
            if ($this->container->getParameter("cnerta_proxy.enabled") === true) {
                $configs['proxy_host'] = $this->container->getParameter("cnerta_proxy.host");
                $configs['proxy_port'] = $this->container->getParameter("cnerta_proxy.port");

                if ($this->container->hasParameter("cnerta_proxy.login") && $this->container->getParameter("cnerta_proxy.login") != null) {
                    $configs['proxy_login'] = $this->container->getParameter("cnerta_proxy.login");
                }
                if ($this->container->hasParameter("cnerta_proxy.password") && $this->container->getParameter("cnerta_proxy.password") != null) {
                    $configs['proxy_password'] = $this->container->getParameter("cnerta_proxy.password");
                }
            }
        }
    }

    /**
     * Set proxy parameters for a CURL connection
     *
     * @param type $resource resource type CURL
     * @return boolean
     *
     * @throws \RuntimeException
     */
    public function setProxyForCURL(&$resource)
    {
        if (get_resource_type($resource) === "curl") {

            if ($this->container->hasParameter("cnerta_proxy.enabled")) {
                if ($this->container->getParameter("cnerta_proxy.enabled") === true) {
                    curl_setopt($resource, CURLOPT_PROXY, $this->container->getParameter("cnerta_proxy.host"));
                    curl_setopt($resource, CURLOPT_PROXYPORT, $this->container->getParameter("cnerta_proxy.port"));

                    if ($this->container->hasParameter("cnerta_proxy.login") && $this->container->getParameter("cnerta_proxy.login") != null) {
                        curl_setopt($resource, CURLOPT_PROXYAUTH, $this->container->getParameter("cnerta_proxy.login") . ':' . $this->container->getParameter("cnerta_proxy.password"));
                    }
                }
            }
            return true;
        } else {
            throw new \RuntimeException('$resource must be a curl resource');
        }

        return false;
    }

}
