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
    public function setProxyForSoapClient(array &$configs, $needContext = false)
    {
        if ($this->parameters["enabled"] === true) {
            $configs['proxy_host'] = $this->parameters["host"];
            $configs['proxy_port'] = $this->parameters["port"];

            if ($this->parameters["login"] !== null) {
                $configs['proxy_login'] = $this->parameters["login"];
            }
            if ($this->parameters["password"] !== null) {
                $configs['proxy_password'] = $this->parameters["password"];
            }

            if($needContext === true) {
                $configs['stream_context'] = $this->getStreamContext(false);
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

                if ($this->parameters["login"] !== null) {
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
            $cxContext = $this->getStreamContext(false);
        }

        return file_get_contents($url, false, $cxContext);
    }

    /**
     * Get configuration for a stream context
     * @param bool $raw If true, return an array with configuration, if false return context resource
     * @return array|null
     */
    public function getStreamContext($raw = true)
    {
        if ($this->parameters["enabled"] === true) {

            $context = array();

            foreach(array('http', 'https') as $protocol) {

                if (array_key_exists($protocol, $this->parameters)) {
                    $httpsProxy = $this->makeTcpUri(
                        $this->parameters[$protocol]["host_proxy"],
                        $this->parameters[$protocol]["port_proxy"]
                    );
                    $context[$protocol]['proxy'] = $httpsProxy;
                    $context[$protocol]['request_fulluri'] = $this->parameters[$protocol]["request_fulluri"];

                    if ($this->parameters[$protocol]['login_proxy'] !== '') {
                        $auth = $this->encodeCredential(
                            $this->parameters[$protocol]["login_proxy"],
                            $this->parameters[$protocol]["password_proxy"]
                        );
                        $context[$protocol]['header'] = "Proxy-Authorization: Basic $auth";
                    }
                }
            }

            if(count($context) === 0) {
                $defaultProxy = $this->makeTcpUri(
                    $this->parameters["host"],
                    $this->parameters["port"]
                );

                if ($defaultProxy) {
                    $context = array(
                        'http' => array(
                            'proxy' => $defaultProxy,
                            'request_fulluri' => true,
                        ),
                        'https' => array(
                            'proxy' => $defaultProxy,
                            'request_fulluri' => true,
                        )
                    );
                }

                if ($this->parameters["login"] !== null) {
                    $auth = $this->encodeCredential($this->parameters["login"], $this->parameters["password"]);
                    $context['http']['header'] = "Proxy-Authorization: Basic $auth";
                    $context['https']['header'] = "Proxy-Authorization: Basic $auth";
                }
            }

            return $raw
                ? $context
                : stream_context_create($context);
        }

        return null;
    }

    /**
     * @param string $login
     * @param string $password
     * @return string
     */
    private function encodeCredential($login, $password)
    {
        return base64_encode($login . ':' . $password);
    }

    /**
     * Make TCP URL
     * Exemple
     * tcp://127.0.0.1
     * tcp://127.0.0.1:8080
     *
     * @param string|null $host
     * @param int|null $port
     * @return null|string
     */
    private function makeTcpUri($host = null, $port = null)
    {
        if($host === null || $host === '') {
            return null;
        }

        $uri = "tcp://" . $host;

        if($port !== null) {
            $uri .= ":" . $port;
        }

        return $uri;
    }

}
