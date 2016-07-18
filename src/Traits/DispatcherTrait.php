<?php

namespace PHPLegends\Routes\Traits;

use PHPLegends\Routes\Route;
use PHPLegends\Routes\Router;

/**
 * 
 * 
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
 * */
trait DispatcherTrait
{
    /**
     * 
     * @param PHPLegends\Routes\Router $router
     * @param PHPLegends\Routes\Route $route
     * @return mixed
     * */
    protected function callRouteFilters(Router $router, Route $route)
    {
        if ($result = $router->getFilters()->processRouteFilters($route)) {
            
            return $result;
        }
    }

    /**
     * 
     * @param \PHPLegends\Routes\Route $route
     * @return mixed
     * */
    protected function callRouteAction(Route $route)
    {
        $callable = $this->buildRouteAction($route);

        return call_user_func_array($callable, $route->getParameters());
    }

    /**
     * 
     * @param \PHPLegends\Routes\Route $route
     * @return callable
     * */
    protected function buildRouteAction(Route $route)
    {
        $action = $route->getAction();

        if ($action instanceof \Closure) {

            return $action;
        }

        return [new $action[0], $action[1]];
    }
}

