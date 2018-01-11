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
    /** @var Proxy $httpProxy */
    private $httpProxy;

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

        $this->httpProxy = new Proxy($parameters);
    }

    /**
     * send
     */
    public function send()
    {
        $data = $this->httpProxy->crawl();

        if (1 === (int) $data['cache']) {
            header('X-Proxy-Cache: 1');
        }

        header('X-Proxy-Date: ' . $data['date']);

        echo $data['content'];
    }
}
