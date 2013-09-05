<?php

require dirname(__DIR__) . '/vendor/autoload.php';

declare(ticks = 1);

$cluster = new \Pagon\Cluster(array(
    'pids_dir'     => true,
    'auto_restart' => true
));

if ($cluster->isMaster()) {
    $cluster->on('exit', function (\Pagon\Worker $worker, $code) use ($cluster) {
        echo getmypid() . ':' . ' worker exited: ' . $code . PHP_EOL;
        //$worker->restart();
    });

    for ($i = 0; $i < 5; $i++) {
        $cluster->fork(__FILE__);
    }

    $cluster->run();
} else {
    /** @var $process \Pagon\Process */
    $process->on('exit', function () {
        echo getmypid() . ':' . ' i am quit' . PHP_EOL;
    });

    echo getmypid() . ':' . ' i am work' . PHP_EOL;
    sleep('5');
}