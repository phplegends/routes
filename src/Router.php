<?php

namespace PHPLegends\Routes;


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
	 * @var \PHPLegends\Routes\RouteCollection
	 *
	 * */
	protected $routes;

	/**
	 * 
	 * @var PHPLegends\Routes\FilterCollection
	 * */

	protected $filters;

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
	 * 
	 * @param \PHPLegends\Routes\Route | null $routes
	 * @param array $options
	 * */
	public function __construct(Collection $routes = null, array $options = [])
	{	
		$this->routes = $routes ?: new Collection;

		$this->filters = new FilterCollection;

		$options && $this->setOptions($options);
	}



	/**
	 * @param string $uri
	 * @return Route | null
	 * */
	public function findByUri($uri)
	{	
		return $this->routes->first(function ($route) use($uri) {
			return $route->match($uri) !== false;
		});

	}

	public function dispatch(Dispatchable $dispatcher)
	{
		return $dispatcher->dispatch($this);
	}

	public function findByUriAndVerb($uri, $verb)
	{
		return $this->routes->first(function ($route) use ($uri, $verb) {
			return $route->match($uri) !== false && $route->acceptedVerb($verb);
		});
	}

	/**
	 * Returns route by given name
	 * 
	 * @param string $name
	*/
	public function findByName($name)
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
	 * @param string $action
	 * @param null | string $name
	 * @return \PHPLegends\Routes\Route
	 * */

	public function addRoute(array $verbs, $pattern, $action, $name = null)
	{

		$pattern = $this->resolvePatternValue($pattern);
		
		$action  = $this->resolveActionValue($action);
		
		$name    = $this->resolveNameValue($name);
		
		$route   = new Route($pattern, $action, $verbs, $name);

		if ($filters = $this->getDefaultFilters()) {

			$route->setFilters($filters);
		}

		$this->routes->add($route);

		return $route;
	}

	public function get($pattern, $action, $name = null)
	{
		return $this->addRoute(['GET', 'HEAD'], $pattern, $action, $name);
	}

	public function put($pattern, $action, $name = null)
	{
		return $this->addRoute(['PUT'], $pattern, $action, $name);
	}

	public function post($pattern, $action, $name = null)
	{
		return $this->addRoute(['POST'], $pattern, $action, $name);
	}

	public function delete($pattern, $action, $name = null)
	{
		return $this->addRoute(['DELETE'], $pattern, $action, $name);
	}

	public function getCollection()
	{
		return $this->routes;
	}

	public function getFilters()
	{
		return $this->filters;
	}    

	public function addFilter($name, callable $callback)
	{
		$filter = new Filter($name, $callback);

		$this->filters->add($filter);

		return $filter;
	}


	public function group(array $options, \Closure $closure)
	{
		$group = new static(null, $options);

		$closure->bindTo($group)->__invoke($group);

		$this->getCollection()->addAll($group->getCollection());

		return $this;

	}

    /**
     * Gets the value of namespace.
     *
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Sets the value of namespace.
     *
     * @param string $namespace the namespace
     *
     * @return self
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }


    protected function resolveActionValue($action)
    {
    	if (is_string($action) && $namespace = $this->getNamespace()) {

 			return rtrim($namespace, '\\') . '\\' . $action;
    	}

    	return $action;
    }

    protected function resolvePatternValue($pattern)
    {
    	if ($prefix = $this->getPrefixPath()) {

    		$pattern = $prefix . $pattern;
    	}

    	return $pattern === '' ? '/' : $pattern;
    }

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
     * Gets the value of defaultFilters.
     *
     * @return mixed
     */
    public function getDefaultFilters()
    {
        return $this->defaultFilters;
    }

    /**
     * Sets the value of defaultFilters.
     *
     * @param array|string $defaultFilters the default filters
     *
     * @return self
     */
    public function setDefaultFilters($defaultFilters)
    {
        $this->defaultFilters = (array) $defaultFilters;

        return $this;
    }

    public function setOptions(array $args)
    {

    	$args += [
			'filters'    => [],
			'name'      => null,
			'namespace' => null,
			'prefix'    => null,
    	];

    	$args['namespace'] && $this->setNamespace($args['namespace']);
    	
    	$args['prefix'] && $this->setPrefixPath($args['prefix']);
    	
    	$args['name'] && $this->setPrefixName($args['name']);
    	
    	$args['filters'] && $this->setDefaultFilters($args['filters']);

    	return $this;    
    }
}