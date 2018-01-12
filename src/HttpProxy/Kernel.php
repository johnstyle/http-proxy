<?php

namespace HttpProxy;

/**
 * Class Kernel
 *
 * @author  Jonathan SAHM <contact@johnstyle.fr>
 * @package HttpProxy
 */
class Kernel
{
    /** @var Proxy $proxy */
    private $proxy;

    /**
     * Kernel constructor.
     */
    public function __construct()
    {
        $parameters = [];
        foreach ($_GET as $name => $value) {
            $value = trim($value);
            if ('' === $value) {
                continue;
            }
            $parameters[$name] = $value;
        }

        $this->proxy = new Proxy($parameters);
    }

    /**
     * send
     */
    public function send()
    {
        $headers = getallheaders();
        if (!isset($headers['X-Token'])
            || $headers['X-Token'] !== $_SERVER['PROXY_TOKEN']) {
            return;
        }

        $data = $this->proxy->crawl();

        foreach ($data['headers'] as $name => $value) {
            header($name . ': ' . $value);
        }

        header('X-Robots-Tag: noindex, nofollow', true);

        echo $data['body'];
    }
}
