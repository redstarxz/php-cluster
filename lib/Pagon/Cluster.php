<?php

namespace Pagon;

use Pagon\Cluster\Middleware;

declare(ticks = 1);

class Cluster extends EventEmitter
{
    /**
     * @var int
     */
    public $pid;

    /**
     * @var ChildProcess
     */
    public $manager;

    /**
     * @var array options for cluster
     */
    protected $options = array(
        'max_children' => 0,
        'auto_restart' => false,
        'stacks'       => array()
    );

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
     * @return Cluster
     */
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;
        $this->manager = ChildProcess::self();
        $this->pid = $this->manager->pid;
    }

    /**
     * Set max children
     *
     * @param $num
     * @return $this
     */
    public function setMaxChildren($num)
    {
        $this->options['max_children'] = $num;
        return $this;
    }

    /**
     * Add middleware
     *
     * @param string|Cluster\Middleware $stack
     * @param array                     $options
     * @return $this
     */
    public function add($stack, array $options = array())
    {
        $this->options['stacks'][] = array($stack, $options);
        return $this;
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

        // Process middlewares
        foreach ($this->options['stacks'] as $stack) {
            $fn = Middleware::build($stack[0], $stack[1]);
            if (!$fn) throw new \RuntimeException("Error middleware: " . $stack[0]);
            $fn($this);
        }

        // Register tick
        $this->manager->on('tick', function () use ($that) {
            $that->tickCheck();
        });

        // Run workers
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
        $this->emit('tick');
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
}