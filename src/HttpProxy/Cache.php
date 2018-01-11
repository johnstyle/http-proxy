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
    /** @var string $url */
    private $url;

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
        $this->url = $url;
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
        $filename = $this->getFilename();

        $data = [
            'cache'   => false,
            'date'    => (new \DateTime())->format('c'),
            'content' => null,
        ];

        if (file_exists($filename)
            || filemtime($filename) > time() - (3600 * 24 * $this->cacheDays)) {
            $data['cache']   = true;
            $data['date']    = (new \DateTime())->setTimestamp(filemtime($filename))->format('c');
            $data['content'] = file_get_contents($filename);
        } else {
            $data['content'] = $callback();
            file_put_contents($filename, $data['content']);
        }

        return $data;
    }

    /**
     * getFilename
     *
     * @return string
     */
    private function getFilename(): string
    {
        return CACHE_DIR . '/' . $this->hash;
    }
}
