<?php

require realpath(__DIR__) . '/../src/bootstrap.php';

(new HttpProxy\Kernel())->run();
