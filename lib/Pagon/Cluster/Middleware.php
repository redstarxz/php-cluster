<?php

namespace Pagon\Cluster;

abstract class Middleware
{
    /**
     * @var \Pagon\Cluster
     */
    protected $cluster;

    /**
     * @var array Default options
     */
    protected $options = array();

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;
    }

    /**
     * Build a middleware
     *
     * @param string $name
     * @param array  $options
     * @return bool|object|\Closure
     */
    public static function build($name, array $options = array())
    {
        if (is_object($name)) return $name;

        $classes = array(
            $name,
            __CLASS__ . '\\' . $name
        );

        foreach ($classes as $class) {
            if (is_subclass_of($class, __CLASS__)) {
                return new $class($options);
            }
        }

        return false;
    }

    /**
     * Call to implements
     *
     * @return mixed
     */
    abstract function call();

    /**
     * To create function callback
     *
     * @param $cluster
     */
    public function __invoke($cluster)
    {
        $this->cluster = $cluster;
        $this->call();
    }
}