<?php

require dirname(__DIR__) . '/vendor/autoload.php';

declare(ticks = 1);

$cluster = new \Pagon\Cluster();

if ($cluster->isMaster()) {
    $cluster->add('PidFiles');
    $cluster->add('AutoRestart');

    $cluster->on('fork', function (\Pagon\Worker $worker) use ($cluster) {
        echo $worker->pid . ' - ' . memory_get_usage() . PHP_EOL;
        //$worker->restart();
    });

    $cluster->on('exit', function (\Pagon\Worker $worker, $code) use ($cluster) {
        echo $worker->pid . ' - ' . 'worker exited: ' . $code . PHP_EOL;
        //$worker->restart();
    });

    for ($i = 0; $i < 2; $i++) {
        $cluster->fork(__FILE__);
    }

    $cluster->forever();
} else {
    /** @var $process \Pagon\Process */
    $process->on('exit', function () {
        //echo getmypid() . '-' . ' i am quit' . PHP_EOL;
    });

    echo getmypid() . ' - ' . 'i am work' . PHP_EOL;
    sleep(10);
}