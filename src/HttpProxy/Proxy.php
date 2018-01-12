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
     * @param int $sleep
     *
     * @return array|null
     */
    public function crawl(int $sleep = 0)
    {
        if (!$this->isValidUrl()) {
            return null;
        }

        $url = $this->getParameter('url');

        return (new Cache($url))->getData(function () use ($url, $sleep) {
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
     * @param $name
     *
     * @return null|string
     */
    private function getParameter($name):? string
    {
        return $this->parameters[$name] ?? null;
    }

    /**
     * getInterface
     *
     * @return string
     */
    private function getInterface(): string
    {
        return array_rand(array_map('trim', explode(',', $_SERVER['PROXY_INTERFACES'])));
    }
}
