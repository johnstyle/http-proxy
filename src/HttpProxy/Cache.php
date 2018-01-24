<?php

namespace HttpProxy;

use Unirest\Response;

/**
 * Class Cache
 *
 * @author  Jonathan SAHM <contact@johnstyle.fr>
 * @package HttpProxy
 */
class Cache
{
    /** @var string $hash */
    private $hash;

    /**
     * Cache constructor.
     *
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->hash = md5($url);
    }

    /**
     * getData
     *
     * @param callable    $callback
     * @param null|string $cacheDays
     *
     * @return array
     * @throws \Exception
     */
    public function getData(callable $callback, ?string $cacheDays = null): array
    {
        $data = [
            'type'  => null,
            'date'  => (new \DateTime())->format('c'),
            'cache' => false,
            'body'  => null,
        ];

        $filename = $this->getFilename();

        if (null !== $cacheDays) {
            $cacheParameters = explode('-', $cacheDays, 2);
            $cacheDays = 1 === count($cacheParameters)
                ? (int) $cacheParameters
                : random_int((int) $cacheParameters[0], (int) $cacheParameters[1])
            ;
        }

        if (null !== $cacheDays
            && file_exists($filename)
            && filemtime($filename) > time() - (3600 * 24 * $cacheDays)) {
            $data['body']  = file_get_contents($filename);
            $firstLinePos  = strpos($data['body'], "\n");
            $metadata      = json_decode(base64_decode(substr($data['body'], 0, $firstLinePos)), true);
            $data['type']  = $metadata['type'] ?? null;
            $data['cache'] = true;
            $data['date']  = (new \DateTime())->setTimestamp(filemtime($filename))->format('c');
            $data['body']  = substr($data['body'], $firstLinePos + 1);
        } elseif($response = $callback()) {
            /** @var Response $response */
            $data['body']  = $response->raw_body;
            $data['type']  = is_array($response->headers['Content-Type'])
                ? array_shift($response->headers['Content-Type'])
                : $response->headers['Content-Type']
            ;
            file_put_contents(
                $this->getFilename(true),
                base64_encode(json_encode([
                    'type' => $data['type']
                ])) . "\n" . $data['body']
            );
        }

        return $data;
    }

    /**
     * getFilename
     *
     * @param bool $create
     *
     * @return string
     */
    private function getFilename(bool $create = false): string
    {
        $directory = chunk_split(substr($this->hash, 0, 6), 1, '/');
        $directory = CACHE_DIR . '/' . $directory;

        if (true === $create
            && !is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        return $directory . '/' . $this->hash;
    }
}
