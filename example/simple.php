<?php

require dirname(__DIR__) . '/vendor/autoload.php';

declare(ticks = 1);

$cluster = new \Pagon\Cluster();

if ($cluster->isMaster()) {
    $cluster->fork(__FILE__);

    $cluster->run();
} else {
    echo getmypid() . ':' . ' i am work' . PHP_EOL;
    sleep('5');
}