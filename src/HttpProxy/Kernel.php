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
    /**
     * run
     *
     * @throws \Exception
     */
    public function run()
    {
        $headers = getallheaders();
        if (!isset($headers['X-Token'])
            || $headers['X-Token'] !== $_SERVER['PROXY_TOKEN']) {
            http_response_code(403);
            return;
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                $parameters = [];
                foreach ($_GET as $name => $value) {
                    $value = trim($value);
                    if ('' === $value) {
                        continue;
                    }
                    $parameters[$name] = $value;
                }

                echo $this->proxy($parameters, isset($headers['X-Cache-Days']) ? $headers['X-Cache-Days'] : null);
                break;

            case 'POST':

                $data = json_decode(file_get_contents('php://input'));

                echo $this->preload($data);
                break;
        }
    }

    /**
     * proxy
     *
     * @param array       $parameters
     * @param null|string $cacheDays
     *
     * @return null|string
     * @throws \Exception
     */
    private function proxy(array $parameters, ?string $cacheDays = null):? string
    {
        $data = (new Proxy($parameters))->crawl($cacheDays);

        foreach ($data['headers'] as $name => $value) {
            header($name . ': ' . $value);
        }

        if (!$data['body']) {
            http_response_code(204);
        }

        header('X-Robots-Tag: noindex, nofollow', true);

        return $data['body'];
    }

    /**
     * preload
     *
     * @param array $data
     *
     * @return null|string
     */
    private function preload(array $data):? string
    {
        (new Gearman())->client($data);

        return null;
    }
}
