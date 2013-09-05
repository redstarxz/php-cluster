<?php

require dirname(__DIR__) . '/vendor/autoload.php';

declare(ticks = 1);

$cluster = new \Pagon\Cluster();

$cluster->setMaxChildren(3);

if ($cluster->isMaster()) {
    $cluster->on('exit', function ($worker, $code) use ($cluster) {
        echo getmypid() . ':' . ' worker exited: ' . $code . PHP_EOL;
        $worker->restart();
    });

    for ($i = 0; $i < 10; $i++) {
        $cluster->fork(__FILE__);
    }

    $cluster->run();
} else {
    $master->on('exit', function() {
        echo getmypid() . ':' . ' i am quit' . PHP_EOL;
    });

    echo getmypid() . ':' . ' i am work' . PHP_EOL;
    sleep('5');
    echo getmypid() . ':' . ' i am work out' . PHP_EOL;
}