<?php

namespace Pagon;

declare(ticks = 1);

class Worker extends EventEmitter
{
    public $pid;
    public $ppid;

    /**
     * @var string
     */
    public $file;

    /**
     * @var Process
     */
    public $child;

    /**
     * @var Cluster
     */
    public $cluster;

    /**
     * @var boolean
     */
    protected $status;

    /**
     * @var boolean
     */
    protected $online = false;

    public function __construct($file, Cluster $cluster)
    {
        $this->cluster = $cluster;
        $this->file = $file;
    }

    /**
     * Birth by child process
     *
     * @param Process $child
     */
    public function birth(Process $child)
    {
        $that = $this;
        $child->on('message', function ($message) use ($that) {
            $that->emit('message', $message);
            $that->cluster->emit('message', $that, $message);
        });

        $child->on('exit', function ($code) use ($that) {
            $that->quit($code);
        });

        $child->on('fork', function () use ($that) {
            $that->sync();
        });

        $this->child = $child;
    }

    /**
     * Sync
     */
    public function sync()
    {
        $this->pid = $this->child->pid;
        $this->ppid = $this->child->ppid;

        $this->emit('fork');
        $this->cluster->emit('fork', $this);
    }

    /**
     * Quit
     *
     * @param $code
     */
    public function quit($code)
    {
        $this->status = $code;

        $this->emit('exit', $code);
        $this->cluster->emit('exit', $this, $code);
    }

    /**
     * Online
     */
    public function online()
    {
        $this->online = true;
        $this->emit('online');
    }

    /**
     * Run
     */
    public function run()
    {
        $this->child->run();
    }

    /**
     * Restart worker
     */
    public function restart()
    {
        $this->cluster->restart($this);
    }

    /**
     * Check if fork?
     *
     * @return bool
     */
    public function isFork()
    {
        return !!$this->pid;
    }

    /**
     * Check if exit?
     *
     * @return bool
     */
    public function isExit()
    {
        return $this->status !== null;
    }

    /**
     * Check if online?
     *
     * @return bool
     */
    public function isOnline()
    {
        return $this->online;
    }
}