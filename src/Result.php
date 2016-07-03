<?php

namespace PHPLegends\Routes;

/**
 * Represents the result of route
 * 
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
 * 
 * */
class Result
{
    /**
     * 
     * @var array
     * */
    protected $arguments = [];

    /**
     * 
     * @var callable
     * */
    protected $callback;

    /**
     * 
     * 
     * @param callable $callback
     * @param array $arguments
     * */
    public function __construct(callable $callback, array $arguments = [])
    {
        $this->callback = $callback;

        $this->arguments = $arguments;
    }

    /**
     * Calls the action of route!
     * 
     * @return mixed
     * */
    public function invoke()
    {
        return call_user_func_array($this->callback, $this->arguments);
    }

    /**
     * Calls the action of route!
     * 
     * @return mixed
     * */
    
    public function __invoke()
    {
        return $this->invoke();
    }

    /**
     * 
     * @return boolean
     * */
    public function isClosure()
    {
        return $this->callback instanceof \Closure;
    }

    /**
     * 
     * @return array
     * */
    public function getArguments()
    {
        return $this->arguments;
    }

}