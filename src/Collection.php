<?php

namespace PHPLegends\Routes;

use PHPLegends\Collections\ListCollection;

/**
 * Collection of Routes
 * 
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
 * */
class Collection extends ListCollection
{
    /**
     * 
     * 
     * @param \PHPLegends\Routes\Route $route
     * @return self
    */
    public function attach(Route $route)
    {
        $this->add($route);

        return $this;
    }

    /**
     * Adds route in collection 
     * 
     * @param Route $route
     * @throws |UnexpectedValueException if non Route instance passed
     * */
    public function add($route)
    {
        if (! $route instanceof Route) {

            throw new \UnexpectedValueException('Only Route can be added');
        }

        return parent::add($route);
    }

    /**
     * 
     * @param string|array $verb
     * @return \PHPlegends\Route\Route | null
    */
    public function findByVerb($verb)
    {
        return $this->first($this->getVerbFilter($verb));
    }

    /**
     * 
     * @param string $pattern
     * @return \PHPlegends\Route\Route | null
    */
    public function findByUri($pattern)
    {
        return $this->first($this->getUriFilter($pattern));
    }

    /**
     * 
     * @param string $uri
     * @return \PHPLegends\Routes\Router
     * */
    public function filterByUri($uri)
    {
        return $this->filter($this->getUriFilter($uri));
    }

    /**
     * 
     * @param string $pattern
     * @return \PHPLegends\Routes\Router
     * */
    public function filterByVerb($verb)
    {
        return $this->filter($this->getVerbFilter($verb));
    }

    /**
     * 
     * @param string $verb
     * @return \Closure
     * */
    protected function getVerbFilter($verb)
    {
        return function (Route $route) use ($verb) {

            return $route->acceptedVerb($verb);
        };
    }

    /**
     * 
     * 
     * @param string $uri
     * @return \Closure
     * */
    protected function getUriFilter($uri)
    {
        return function (Route $route) use ($uri) {

            return $route->isValid($uri);
        };
    }

    /**
     * 
     * @param callable $callback
     * @return Listcollection
     * */
    public function map(callable $callback = null)
    {   
        $items = array_map(
            $callback,
            $this->all(),
            $keys = $this->keys()
        );

        return new ListCollection(array_combine($keys, $items));
    }

    /**
     * Filter by prefix name
     *  
     * @param string $name
     * @return \PHPLegends\Routes\Collection
     * */
    public function filterByPrefixName($name)
    {
        return $this->filter(function (Route $route) use ($name)
        {
            return strpos($route->getName(), $name) === 0;
        });
    }

}