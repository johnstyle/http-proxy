<?php

namespace HttpProxy;

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

    /** @var int $cacheDays */
    private $cacheDays;

    /**
     * Cache constructor.
     *
     * @param string $url
     * @param int    $cacheDays
     */
    public function __construct(string $url, int $cacheDays = 30)
    {
        $this->hash = md5($url);
        $this->cacheDays = $cacheDays;
    }

    /**
     * getData
     *
     * @param callable $callback
     *
     * @return array
     */
    public function getData(callable $callback): array
    {
        $data = [
            'headers' => [
                'Content-Type'  => null,
                'X-Proxy-Cache' => 0,
                'X-Proxy-Date'  => (new \DateTime())->format('c'),
            ],
            'body' => null,
        ];

        $filename = $this->getFilename();

        if (file_exists($filename)
            && filemtime($filename) > time() - (3600 * 24 * $this->cacheDays)) {
            $data['body'] = file_get_contents($filename);
            $firstLine = strpos($data['body'], "\n");
            $data['headers'] = [
                'Content-Type'  => substr($data['body'], 0, $firstLine),
                'X-Proxy-Cache' => 1,
                'X-Proxy-Date'  => (new \DateTime())->setTimestamp(filemtime($filename))->format('c'),
            ];
            $data['body'] = substr($data['body'], $firstLine + 1);
        } elseif($response = $callback()) {
            $data['body'] = $response->body;
            $data['headers']['Content-Type'] = is_array($response->headers['Content-Type'])
                ? array_shift($response->headers['Content-Type'])
                : $response->headers['Content-Type']
            ;
            file_put_contents($this->getFilename(true), $data['headers']['Content-Type'] . "\n" . $data['body']);
        }

        if ('' === (string) $data['headers']['Content-Type']) {
            $data['headers']['Content-Type'] = 'plain/text';
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
