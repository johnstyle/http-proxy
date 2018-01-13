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
     * @param callable $callback
     *
     * @return array
     * @throws \Exception
     */
    public function getData(callable $callback): array
    {
        $data = [
            'type'  => null,
            'date'  => (new \DateTime())->format('c'),
            'cache' => false,
            'body'  => null,
        ];

        $filename = $this->getFilename();

        if (file_exists($filename)
            && filemtime($filename) > time() - (3600 * 24 * random_int(10, 30))) {
            $data['body']  = file_get_contents($filename);
            $firstLine     = strpos($data['body'], "\n");
            $data['type']  = substr($data['body'], 0, $firstLine);
            $data['cache'] = true;
            $data['date']  = (new \DateTime())->setTimestamp(filemtime($filename))->format('c');
            $data['body']  = substr($data['body'], $firstLine + 1);
        } elseif($response = $callback()) {
            /** @var Response $response */
            $data['body']  = $response->raw_body;
            $data['type']  = is_array($response->headers['Content-Type'])
                ? array_shift($response->headers['Content-Type'])
                : $response->headers['Content-Type']
            ;
            file_put_contents($this->getFilename(true), $data['type'] . "\n" . $data['body']);
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
