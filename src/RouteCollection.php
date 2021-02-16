<?php

namespace PHPLegends\Routes;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use PHPLegends\Collections\Collection;

/**
 * Collection of Routes
 * 
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
 * */
class RouteCollection implements IteratorAggregate, Countable
{

    /**
     * routes
     *
     * @var array
     */
    protected $routes = [];

    /**
     *
     * @param array $routes
     */
    public function __construct(array $routes = [])
    {
        $this->merge(...$routes);
    }

    /**
     * 
     * @param \PHPLegends\Routes\Route $route
     * @return self
    */
    public function attach(Route $route)
    {
        $this->routes[] = $route;

        return $this;
    }

    /**
     * 
     * @param string|array $verb
     * @return \PHPlegends\Route\Route|null
    */
    public function findByVerb(string $verb)
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
    protected function getUriFilter(string $uri)
    {
        return function (Route $route) use ($uri) {
            return $route->match($uri);
        };
    }


    /**
     * Filter by prefix name
     *  
     * @param string $name
     * @return self
     * */
    public function filterByPrefixName($name)
    {
        return $this->filter(function (Route $route) use ($name) {
            return strpos($route->getName(), $name) === 0;
        });
    }

    /**
     * Filter by prefix name
     *  
     * @param string $name
     * @return \PHPLegends\Routes\Collection
     * */
    public function filterByName($name)
    {
        return $this->filter(function (Route $route) use ($name)
        {
            return $route->getName() === $name;
        });
    }

    /**
     * Filter by prefix name
     *  
     * @param string $name
     * @return \PHPLegends\Routes\Route
     * */
    public function findByName($name)
    {
        return $this->filterByName($name)->first();
    }

    /**
     *      
     * 
     * @param callable|null $callback
     * @return \PHPLegends\Routes\Route
     * @throws PHPLegends\Routes\Exceptions\NotFoundException
     * */
    public function firstOrFail(callable $callback = null)
    {
        $route = $this->first($callback);

        if ($route === null) {

            throw new Exceptions\NotFoundException('Route not found');
        }

        return $route;
    }

    public function filter(callable $callback = null): self
    {
        $routes = array_filter($this->all(), $callback);

        return new static($routes);
    }

    public function count()
    {
        return count($this->all());
    }

    public function getIterator()
    {
        return new ArrayIterator($this->all());
    }
    
    public function all(): array
    {
        return $this->routes;
    }

    public function first(callable $callback)
    {
        foreach ($this->all() as $route) {
            if ($callback($route)) return $route;        
        }
    }

    public function merge(Route ...$routes)
    {
        $this->routes = array_merge($this->routes, $routes);

        return $this;
    }

    public function map(callable $callback): array
    {
        return array_map($callback, $this->all());
    }

}