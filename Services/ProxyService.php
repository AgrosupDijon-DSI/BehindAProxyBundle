<?php

namespace Cnerta\ProxyBundle\Services;

/**
 * @author Valérian Girard <valerian.girard@educagri.fr>
 */
class ProxyService
{

    /**
     * @var array
     */
    private $parameters;

    public function __construct($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Set proxy parameters for a SoapClient connection
     *
     * @param array $configs
     */
    public function setProxyForSoapClient(array &$configs)
    {
            if ($this->parameters["enabled"] === true) {
                $configs['proxy_host'] = $this->parameters["host"];
                $configs['proxy_port'] = $this->parameters["port"];

                if ($this->parameters["login"] != null) {
                    $configs['proxy_login'] = $this->parameters["login"];
                }
                if ($this->parameters["password"] != null) {
                    $configs['proxy_password'] = $this->parameters["password"];
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

            
                if ($this->parameters["enabled"] === true) {
                    curl_setopt($resource, CURLOPT_PROXY, $this->parameters["host"]);
                    curl_setopt($resource, CURLOPT_PROXYPORT, $this->parameters["port"]);

                    if ($this->parameters["login"] != null) {
                        curl_setopt($resource, CURLOPT_PROXYAUTH, $this->parameters["login"] . ':' . $this->parameters["password"]);
                    }
                }
            
            return true;
        } else {
            throw new \RuntimeException('$resource must be a curl resource');
        }

        return false;
    }

}
