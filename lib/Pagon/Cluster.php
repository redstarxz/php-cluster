<?php

namespace Pagon;

declare(ticks = 1);

class Cluster extends EventEmitter
{
    /**
     * @var array options for cluster
     */
    protected $options = array(
        'max_children' => 0,
    );

    /**
     * @var ChildProcess
     */
    protected $manager;

    /**
     * @var Worker[]
     */
    protected $workers = array();

    public function __construct()
    {
        $this->manager = ChildProcess::self();
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
        $child = $this->manager->fork($file, false);
        $worker->init($child);
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
        $child = $this->manager->fork($worker->file, false);
        $worker->init($child);
        $worker->run();
        return $worker;
    }

    /**
     * Run
     */
    public function run()
    {
        $that = $this;
        $this->manager->on('tick', function () use ($that) {
            $that->tickCheck();
        });

        foreach ($this->workers as $worker) {
            $worker->run();
        }

        while (1) {
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
        foreach ($this->workers as $index => $worker) {
            if ($worker->isExit()) {
                unset($this->workers[$index]);
            }

            if ($worker->isFork() && !$worker->isOnline()) {
                $worker->online();
            }
        }
    }
}