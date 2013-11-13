<?php

namespace Cnerta\BehindAProxyBundle\Services;

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
     * Retur true if the stream context default is set
     * @return boolean
     */
    public function loadDefaultStreamContext()
    {
        if ($this->parameters["enabled"] === true && $this->parameters["load_default_stream_context"]) {
            stream_context_set_default($this->getStreamContext());
            return true;
        }
        return false;
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
        if (is_resource($resource) && get_resource_type($resource) === "curl") {

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

    /**
     * Method php file_get_contents through a proxy
     *
     * @param string $url
     * @return string
     */
    public function fileGetContent($url)
    {
        $cxContext = null;

        if ($this->parameters["enabled"] === true) {
            $context = $this->getStreamContext();

            $cxContext = stream_context_create($context);
        }

        return file_get_contents($url, false, $cxContext);
    }

    /**
     * Get configuration for a stream context
     *
     * @see http://pas-bien.net/blog/2007/12/21/utilisation-avancee-de-file_get_contents-php-5
     * @return array
     */
    private function getStreamContext()
    {
        if ($this->parameters["enabled"] === true) {
            $context = array(
                'http' => array(
                    'proxy' => 'tcp://' . $this->parameters["host"] . ':' . $this->parameters["port"],
                    'request_fulluri' => true,
                )
            );

            if ($this->parameters["login"] != null) {
                $auth = base64_encode($this->parameters["login"] . ':' . $this->parameters["password"]);
                $context['http']['header'] = "Proxy-Authorization: Basic $auth";
                $context['https']['header'] = "Proxy-Authorization: Basic $auth";
            }

            return $context;
        }

        return null;
    }

}
