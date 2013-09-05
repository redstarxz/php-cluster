<?php

namespace Pagon\Cluster\Middleware;

use Pagon\Cluster\Middleware;
use Pagon\Worker;

/**
 * PidFiles.php.
 */

class PidFiles extends Middleware
{
    protected $options = array(
        'dir' => './.pids'
    );

    function call()
    {
        $that = $this;

        $this->savePid('master', $this->cluster->pid);

        // When manager exit
        $this->cluster->on('exit', function () use ($that) {
            $that->delPid('master');
        });

        // When fork
        $this->cluster->on('fork', function (Worker $worker) use ($that) {
            $that->savePid($worker->id, $worker->pid);
        });

        // When exit
        $this->cluster->on('exit', function (Worker $worker) use ($that) {
            $that->delPid($worker->id);
        });
    }

    /**
     * Save Pid
     *
     * @param string $name
     * @param int    $pid
     * @return bool|int
     */
    public function savePid($name, $pid)
    {
        if (!$this->options['dir']) return false;

        $dir = $this->options['dir'];

        if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
            return false;
        }

        return file_put_contents($dir . '/' . $name . '.pid', $pid);
    }

    /**
     * Save Pid
     *
     * @param string $name
     * @return bool|int
     */
    public function delPid($name)
    {
        if (!$this->options['dir']) return false;

        $file = $this->options['dir'] . '/' . $name . '.pid';

        if (is_file($file)) {
            return unlink($file);
        }
        return false;
    }
}