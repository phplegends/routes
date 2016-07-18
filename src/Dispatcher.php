<?php

namespace PHPLegends\Routes;
use PHPLegends\Routes\Exceptions\NotFoundException;
use PHPLegends\Routes\Exceptions\InvalidVerbException;

/**
 * Simple dispatcher using Dispatchable implementation
 * 
 * @author Wallace de Souza Vizerra
 * 
 * */
class Dispatcher implements Dispatchable
{   
    use Traits\DispatcherTrait;

    /**
     * 
     * @var string
     * */
    protected $uri;

    /**
     * 
     * @param string|null
     * */
    protected $verb;
    /**
     * 
     * @param string $uri
     * @param string $verb
     * */
    public function __construct($uri, $verb)
    {
        $this->uri = $uri;

        $this->verb = $verb;
    }

    /**
     * 
     * @{inheritdoc}
     * */
    public function dispatch(Router $router)
    {
        $route = $router->findRoute($this->uri, $this->verb);

        $filterResult = $this->callRouteFilters($router, $route);

        return $filterResult === null ? $this->callRouteAction($route) : $filterResult;
        
    }
}