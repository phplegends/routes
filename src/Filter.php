<?php

namespace PHPLegends\Routes;

/**
 * 
 * 
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
 * */

class Filter
{
    /**
     * 
     * @var callable
     * */
    protected $callback;

    /**
     * 
     * @var string
     * */
    protected $name;

    /**
     * 
     * 
     * @param string $name
     * @param callable $callback
     * */
    public function __construct($name, callable $callback)
    {
        $this->name = $name;

        $this->callback = $callback;
    }

    /**
     * 
     * @return callable
     * */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Gets the name value
     * 
     * @return string
     * */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Invoke the callable of filter
     * 
     * @param ... $args
     * @return mixed
     * */
    public function invoke()
    {
        return call_user_func_array($this->getCallback(), func_get_args());
    }

    /**
     * Invoke the callable of filter
     * 
     * @param ... $args
     * @return mixed
     * */
    public function __invoke()
    {
        return call_user_func_array($this->getCallback(), func_get_args());
    }
}