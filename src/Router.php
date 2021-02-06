<?php

namespace PHPLegends\Routes;

use PHPLegends\Routes\Exceptions\NotFoundException;
use PHPLegends\Routes\Exceptions\InvalidVerbException;

/**
 * This class is a tool for easy way for create routes in collection
 *
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
 *
 * */

class Router
{

	/**
	 * @var string
	 *
	 * */
	protected $prefixPath = '';

	/**
	 * @var \PHPLegends\Routes\Collection
	 *
	 * */
	protected $routes;


	/**
	 *
	 * @var string
	 * */
	protected $namespace;

	/**
	 *
	 * @var string
	 * */
	protected $prefixName;

	/**
	 * Default filters name for created route in collection
	 *
	 * @var array
	 * */
	protected $defaultFilters = [];

    /**
     * 
     * @var array
     * */
    protected $options  = [];

	/**
	 *
	 * @param \PHPLegends\Routes\Collection | null $routes
	 * @param array $options
	 * */
	public function __construct(Collection $routes = null, array $options = [])
	{
		$this->routes = $routes ?? new Collection;

		$options && $this->setOptions($options);
	}

	/**
	 * Finds the route via uri
	 *
	 * @param string $uri
	 * @return \PHPLegends\Routes\Route | null
	 * */
	public function findByUri($uri)
	{
		return $this->routes->first(function (Route $route) use($uri) {
			return $route->match($uri);
		});
	}


	/**
	 *
	 * @param string $uri
	 * @param string $verb
	 * @return \PHPLegends\Routes\Route | null
	 * */
	public function findByUriAndVerb(string $uri, string $verb)
	{
		return $this->routes->first(function ($route) use ($uri, $verb) {
			return $route->acceptedVerb($verb) && $route->match($uri);
		});
	}

    /**
     * 
     * @param string $uri
     * @param string $verb
     * @return PHPLegends\Routes\Route|null
     * */
    public function findRoute(string $uri, string $verb)
    {   
		$routes = $this->getCollection()->filterByUri($uri);
		
        if ($route = $routes->findByVerb($verb)) {
            return $route;
        }

        throw new InvalidVerbException(sprintf('Invalid verb %s', strtoupper($verb)));
    }

	/**
	 * Returns route by given name
	 *
	 * @param string $name
	 * @return \PHPLegends\Routes\Route | null
	*/
	public function findByName(string $name)
	{
		return $this->routes->first(function ($route) use($name)
		{
			return $route->getName() === $name;
		});
	}

	/**
	 * Create a new route instance and attach to Collection
	 *
	 * @param array $verbs
	 * @param string $pattern
	 * @param array|\Closure $action
	 * @param null|string $name
	 * @return \PHPLegends\Routes\Route
	 * */
	public function addRoute(array $verbs, $pattern, $action, $name = null)
	{

		$pattern = $this->resolvePatternValue($pattern);
		$name = $this->resolveNameValue($name);

		$route = new Route($pattern, $action, $verbs, $name);

		$this->routes->attach($route);

		return $route;
	}

	/**
	 * Create new row and add in collection
	 *
	 * @param string $pattern
	 * @param array|\Closure $action
	 * @param string|null $name
	 * */
	public function get(string $pattern, $action, ?string $name = null)
	{
		return $this->addRoute(['GET', 'HEAD'], $pattern, $action, $name);
	}

	/**
	 * Create new row and add in collection
	 *
	 * @param string $pattern
	 * @param string|\Closure $action
	 * @param string|null $name
	 * */
	public function put(string $pattern, $action, ?string $name = null)
	{
		return $this->addRoute(['PUT'], $pattern, $action, $name);
	}

	/**
	 * Create new row and add in collection
	 *
	 * @param string $pattern
	 * @param string|\Closure $action
	 * @param string|null $name
	 * */
	public function post(string $pattern, $action, ?string $name = null)
	{
		return $this->addRoute(['POST'], $pattern, $action, $name);
	}

	/**
	 * Create new row and add in collection
	 *
	 * @param string $pattern
	 * @param string|\Closure $action
	 * @param string|null $name
	 * */
	public function delete($pattern, $action, $name = null)
	{
		return $this->addRoute(['DELETE'], $pattern, $action, $name);
	}

    /**
     * Create new row and add in collection
     *
     * @param string $pattern
     * @param string|\Closure $action
     * @param string|null $name
     * */

    public function trace($pattern, $action, $name =  null)
    {
        return $this->addRoute(['TRACE'], $pattern, $action, $name);
    }

    /**
     * Create new row and add in collection
     *
     * @param string $pattern
     * @param string|\Closure $action
     * @param string|null $name
     * */

    public function any($pattern, $action, $name =  null)
    {
        return $this->addRoute(['*'], $pattern, $action, $name);
    }

    /**
     * Create new row and add in collection
     *
     * @param string $pattern
     * @param string|\Closure $action
     * @param string|null $name
     * */
    public function options($pattern, $action, $name = null)
    {
        return $this->addRoute(['OPTIONS'], $pattern, $action, $name);
    }

    /**
     * Create new row and add in collection
     *
     * @param string $pattern
     * @param string|\Closure $action
     * @param string|null $name
     * */
    public function head($pattern, $action, $name = null)
    {
        return $this->addRoute(['HEAD'], $pattern, $action, $name);
    }

	/**
	 * Gets the route collection
	 *
	 * @return \PHPLegends\Routes\Collection
	 * */
	public function getCollection()
	{
		return $this->routes;
	}


	/**
	 * Create a group with specific options
	 *
	 * @param array $options
	 * @param \Closure $closure
	 * */
	public function group(array $options, \Closure $closure)
	{
		$group = new static(null, $options);

		$closure->bindTo($group)->__invoke($group);

		$this->getCollection()->merge(
			$group->getCollection()->all()
		);

		return $this;

	}


    /**
     *
     * @param string $controller
     * @param string|null $prefix
     * */

    public function resource($controller, $prefix = null)
    {

		if ($prefix === null) {
			$prefix = RoutableInspector::buildUriPrefix($controller);
		}

		$this->get($prefix . '/', [$controller, 'index'], $prefix);
        $this->post($prefix . '/', [$controller, 'create'], $prefix . '.create');
        $this->put($prefix . '/{str}', [$controller, 'update'], $prefix . '.update');
        $this->get($prefix . '/{str}', [$controller, 'show'], $prefix . '.show');
		$this->delete($prefix . '/{str}', [$controller, 'delete'], $prefix . '.delete');
	
        return $this;

    }


    protected function resolvePatternValue($pattern)
    {
    	if ($prefix = $this->getPrefixPath()) {

    		$pattern = $prefix . $pattern;
    	}

    	return $pattern === '' ? '/' : $pattern;
    }

    /**
     * Result value of name
     *
     * @param string|null $name
     * @return string|null
     * */
    protected function resolveNameValue($name)
    {
    	if ($name === null) return null;

    	if ($prefixName = $this->getPrefixName()) {

    		$name = $prefixName . $name;
    	}

    	return $name;
    }


    /**
     * Gets the value of prefixPath.
     *
     * @return mixed
     */
    public function getPrefixPath()
    {
        return $this->prefixPath;
    }

    /**
     * Sets the value of prefixPath.
     *
     * @param string $prefixPath
     *
     * @return self
     */
    public function setPrefixPath($prefixPath)
    {
        $this->prefixPath = $prefixPath;

        return $this;
    }

    /**
     * Gets the value of prefixName.
     *
     * @return mixed
     */
    public function getPrefixName()
    {
        return $this->prefixName;
    }

    /**
     * Sets the value of prefixName.
     *
     * @param string $prefixName
     *
     * @return self
     */
    public function setPrefixName($prefixName)
    {
        $this->prefixName = $prefixName;

        return $this;
    }

    /**
     * Set value via array options
     *
     * @param array $args
     * @return self
     * */
    public function setOptions(array $args)
    {
    	$this->options = $args += [
			'name'      => null,
			'prefix'    => null,
    	];

    	$args['prefix'] && $this->setPrefixPath($args['prefix']);

    	$args['name'] && $this->setPrefixName($args['name']);

    	return $this;
    }

}
