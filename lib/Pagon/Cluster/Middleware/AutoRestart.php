<?php

namespace Pagon\Cluster\Middleware;

use Pagon\Cluster\Middleware;
use Pagon\Worker;

/**
 * PidFiles.php.
 */

class AutoRestart extends Middleware
{
    protected $options = array(
        'dir' => './.pids'
    );

    function call()
    {
        $this->cluster->on('finish', function (Worker $worker) {
            $worker->restart();
        });
    }
}