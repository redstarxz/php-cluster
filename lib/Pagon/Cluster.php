<?php

namespace Pagon;

declare(ticks = 1);

class Cluster extends EventEmitter
{
    /**
     * @var int
     */
    public $pid;

    /**
     * @var array options for cluster
     */
    protected $options = array(
        'max_children' => 0,
        'pids_dir'     => false,
        'auto_restart' => false
    );

    /**
     * @var ChildProcess
     */
    protected $manager;

    /**
     * @var Worker[]
     */
    protected $workers = array();

    /**
     * @var bool Is running?
     */
    protected $running = false;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;
        if ($this->options['pids_dir'] === true) $this->options['pids_dir'] = './.pids';
        $this->manager = ChildProcess::self();
        $this->pid = $this->manager->pid;
    }

    /**
     * Set max children
     *
     * @param $num
     */
    public function setMaxChildren($num)
    {
        $this->options['max_children'] = $num;
    }

    /**
     * Fork
     *
     * @param string $file
     * @return bool|Worker
     */
    public function fork($file)
    {
        if ($this->options['max_children']
            && count($this->workers) >= $this->options['max_children']
        ) {
            return false;
        }

        $this->workers[] = $worker = new Worker($file, $this);
        $this->setup($worker, $file);
        return $worker;
    }

    /**
     * Restart worker
     *
     * @param Worker $worker
     * @return Worker
     */
    public function restart(Worker $worker)
    {
        $this->setup($worker, $worker->file);
        $worker->run();
        return $worker;
    }

    /**
     * Setup worker
     *
     * @param Worker $worker
     * @param string $file
     */
    public function setup(Worker $worker, $file)
    {
        $child = $this->manager->fork($file, false);
        $worker->init($child);
    }

    /**
     * Run
     */
    public function run()
    {
        if ($this->running) throw new \RuntimeException("Cluster already running");

        $this->running = true;
        $that = $this;
        $this->savePid('master', $this->pid);

        $this->manager->on('exit', function () use ($that) {
            $that->delPid('master');
        });

        $this->manager->on('tick', function () use ($that) {
            $that->tickCheck();
        });

        // When fork
        $this->on('fork', function (Worker $worker) use ($that) {
            $that->savePid($worker->id, $worker->pid);
        });

        // When exit
        $this->on('exit', function (Worker $worker) use ($that) {
            $that->delPid($worker->id);
        });

        // Support auto restart
        if ($this->options['auto_restart']) {
            $this->on('exit', function (Worker $worker) {
                $worker->restart();
            });
        }

        foreach ($this->workers as $worker) {
            $worker->run();
        }

        while (1) {
            usleep(100);
        }
    }

    /**
     * Is master
     *
     * @return bool
     */
    public function isMaster()
    {
        return $this->manager->isMaster();
    }

    /**
     * Will run when every tick
     */
    public function tickCheck()
    {
        // Loop workers
        foreach ($this->workers as $index => $worker) {
            // Check if online
            if ($worker->isFork() && !$worker->isOnline()) {
                $worker->online();
            }

            // Check if exit?
            if ($worker->isExit()) {
                unset($this->workers[$index]);
                continue;
            }
        }
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
        if (!$this->options['pids_dir']) return false;

        $dir = $this->options['pids_dir'];

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
        if (!$this->options['pids_dir']) return false;

        $file = $this->options['pids_dir'] . '/' . $name . '.pid';

        if (is_file($file)) {
            return unlink($file);
        }
        return false;
    }
}