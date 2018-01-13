<?php

define('ROOT_DIR', realpath(__DIR__) . '/..');
define('CACHE_DIR', ROOT_DIR . '/var/cache');

require ROOT_DIR . '/vendor/autoload.php';

if (file_exists(ROOT_DIR . '/.env')
    && class_exists('Dotenv\\Dotenv')) {
    (new Dotenv\Dotenv(ROOT_DIR))->load();
}
