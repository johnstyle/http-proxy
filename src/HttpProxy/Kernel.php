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
     * send
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

                echo $this->proxy($parameters);
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
     * @param array $parameters
     *
     * @return null|string
     */
    private function proxy(array $parameters):? string
    {
        $data = (new Proxy($parameters))->crawl();

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
