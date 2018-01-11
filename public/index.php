<?php

use HttpProxy\Kernel;

define('ROOT_DIR', realpath(__DIR__) . '/..');
define('CACHE_DIR', ROOT_DIR . '/var/cache');

require ROOT_DIR . '/vendor/autoload.php';

(new Kernel())->send();
