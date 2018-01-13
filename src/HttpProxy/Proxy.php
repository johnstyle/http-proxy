<?php

namespace HttpProxy;

use Unirest\Exception;
use Unirest\Request;

/**
 * Class Proxy
 *
 * @author  Jonathan SAHM <contact@johnstyle.fr>
 * @package HttpProxy
 */
class Proxy
{
    /** @var array $parameters */
    private $parameters;

    /**
     * HttpProxy constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * crawl
     *
     * @return array|null
     * @throws \Exception
     */
    public function crawl()
    {
        if (!$this->isValidUrl()) {
            return null;
        }

        $url = $this->getParameter('url');
        $sleep = (int) $this->getParameter('sleep', 0);

        $data = (new Cache($url))->getData(function () use ($url, $sleep) {
            sleep($sleep);

            try {

                Request::timeout(5);
                Request::curlOpts([
                    CURLOPT_INTERFACE => $this->getInterface(),
                ]);

                $response = Request::get($url, [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Language' => 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Cache-Control' => 'no-cache',
                    'Pragma' => 'no-cache',
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36',
                    'Referer' => $url,
                ]);

                if (200 !== $response->code) {
                    return null;
                }

                return $response;

            } catch (Exception $e) {
                return null;
            }
        });

        return [
            'headers' => [
                'Content-Type'  => $data['type'] ?? 'plain/text',
                'X-Proxy-Url'   => $url,
                'X-Proxy-Date'  => $data['date'],
                'X-Proxy-Sleep' => $data['cache'] ? $sleep : 0,
                'X-Proxy-Cache' => (int) $data['cache'],
            ],
            'body' => $data['body'],
        ];
    }

    /**
     * isValidUrl
     *
     * @return bool
     */
    private function isValidUrl(): bool
    {
        return (bool) preg_match('/^https?:\/\/[[:alnum:]\.\-]+(?:\.[[:alpha:]]+)?(?::\d+)?(?:\/?|\/.+)$/i', $this->getParameter('url'));
    }

    /**
     * getParameter
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    private function getParameter(string $name, $default = null):? string
    {
        return $this->parameters[$name] ?? $default;
    }

    /**
     * getInterface
     *
     * @return string
     */
    private function getInterface(): string
    {
        $interfaces = array_map('trim', explode(',', $_SERVER['PROXY_INTERFACES']));
        return $interfaces[array_rand($interfaces)];
    }
}
