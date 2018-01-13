<?php

namespace HttpProxy;

/**
 * Class Gearman
 *
 * @author  Jonathan SAHM <contact@johnstyle.fr>
 * @package HttpProxy
 */
class Gearman
{
    const CRAWLER_FUNCTION = 'crawler';

    /**
     * client
     *
     * @param array|null $items
     */
    public function client(?array $items): void
    {
        if (!count($items)) {
            return;
        }

        $client = new \GearmanClient();
        $this->setServers($client);
        foreach ($items as $parameters) {
            if (!is_array($parameters)
                || !isset($parameters['url'])) {
                continue;
            }
            $client->doBackground(static::CRAWLER_FUNCTION, json_encode($parameters));
        }
    }

    /**
     * worker
     */
    public function worker(): void
    {
        $worker = new \GearmanWorker();
        $this->setServers($worker);
        $worker->addFunction(static::CRAWLER_FUNCTION, function (\GearmanJob $job) {
            $parameters = json_decode($job->workload(), true);
            $parameters['sleep'] = random_int(60, 3600 * 12);
            (new Proxy($parameters))->crawl();
        });

        while ($worker->work()) {
            $returnCode = $worker->returnCode();
            if ($returnCode !== GEARMAN_SUCCESS) {
                echo 'Bad return code: ' . $returnCode . "\n";
                break;
            }
        }

    }

    /**
     * setServers
     *
     * @param $gearman
     */
    private function setServers(&$gearman): void
    {
        $servers = array_map('trim', explode(',', $_SERVER['GEARMAN_SERVERS']));

        foreach ($servers as $server) {
            $gearman->addServer($server);
        }
    }
}
