Cnerta Behind A Proxy Bundle
============================

Add proxy parameters for CURL, SoapClient connection and PHP function using stream context.


Install the Bundle
------------------

1. Add the sources in your composer.json

```json
     "require": {
        // ...
        "cnerta/proxy-bundle": "master"
    },
    "repositories": [
        {
            "type": "git",
            "url": "git@github.com:AgrosupDijon-Eduter/BehindAProxyBundle.git",
            "branch": "master"
        }
    ]
```

2. Then add it to your AppKernel class::

```php
    // in AppKernel::registerBundles()
    $bundles = array(
        // ...
        new Cnerta\BehindAProxyBundle\CnertaBehindAProxyBundle(),
        // ...
    );
```


Configuration
-------------

config.yml

```yaml

    cnerta_proxy:
        enabled: false                # type: boulean, default value: false, desc: enabled (true), or desabled (false) the use of proxy
        host: 172.0.0.1               # type: string, default value: null, desc : this is the IP or URL of the proxy server
        port: 80                      # type: mixed(string|int), default value: null, desc : this is the port of the proxy server
        host_ssl: 172.0.0.2           # type: string, default value: null, desc : this is the IP or URL of the proxy server for HTTPS/SSL connection
        login: myWonderfulLogin       # type: string, default value: null, desc : this is the login for authentication against the proxy server
        password: myWonderfulLogin    # type: string, default value: null, this is the password for authentication against the proxy server
        load_default_stream_context: false    # type: boolean, default value: false, If you need to set the default proxy config global
```


Set configuration proxy for CURL
--------------------------------

```php
    use Symfony\Component\DependencyInjection\ContainerInterface;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    //...

    $s = curl_init();
    curl_setopt($s, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($s, CURLOPT_FAILONERROR, true);
    curl_setopt($s, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($s, CURLOPT_URL, $this->url);

    // Call cnerta.proxy service and call the method setProxyForCURL
    // the CURL resource '$s' is passed by reference
    $container->get('cnerta.proxy')->setProxyForCURL($s);

    curl_exec($s);
    $status = curl_getinfo($s, CURLINFO_HTTP_CODE);
    $error = curl_error($s);

    curl_close($s);

    if ($status == 401) {
        throw new \RuntimeException("Invalid Credencial to connect to WebService");
    } else if ($status == 404) {
        throw new \RuntimeException("Invalid URL to connect to WebService");
    } elseif ($status != 200) {
        throw new \RuntimeException($error);
    }
```


Set configuration proxy for SoapClient
--------------------------------------

```php

    use Symfony\Component\DependencyInjection\ContainerInterface;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    //...

    $config =  array(
        "trace" => true,
        "exceptions" => 0,
        "cache_wsdl" => WSDL_CACHE_NONE
    );

    $container->get('cnerta.proxy')->setProxyForSoapClient($config);

    $soapClient = new \SoapClient('http://www.somewhere.com/?wsdl', $config);
```


Get Parameters anywhere
-----------------------
```php
    use Symfony\Component\DependencyInjection\ContainerInterface;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    //...

    $this->container->getParameter("cnerta_proxy.enabled")
    $this->container->getParameter("cnerta_proxy.host")
    $this->container->getParameter("cnerta_proxy.port")
    $this->container->getParameter("cnerta_proxy.host_ssl")
    $this->container->getParameter("cnerta_proxy.login")
    $this->container->getParameter("cnerta_proxy.password")

```
