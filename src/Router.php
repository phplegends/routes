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
	protected $basePath = '';	

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

	public function __construct(Collection $routes = null)
	{	
		$this->routes = $routes ?: new Collection;

		$this->filters = new FilterCollection;
	}

	public function setBasepath($basePath)
	{
		$this->basePath = rtrim($basePath, '/');

		return $this;
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
		$dispatcher->setRouter($this);

		try {

			$result = $dispatcher->dispatch();

		} catch (\Exception $e) {

			return $dispatcher->handleException($e);
		}

		return $result;
	}

	public function findByUriAndVerb($uri, $verb)
	{
		return $this->routes->first(function ($route) use ($uri, $verb) {
			return $route->match($uri) !== false && $route->acceptedVerb($verb);
		});
	}

	/**
	* Returns route by given name
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
	 * @return \PHPLegends\Routes\Route
	 * */

	public function addRoute(array $verbs, $pattern, $action)
	{

		$pattern = trim($this->basePath . '/' . $pattern, '/');

		$newRoute = new Route($pattern, $action);

		$newRoute->setVerbs($verbs);

		$this->routes->add($newRoute);

		return $newRoute;
	}

	public function get($pattern, $action)
	{
		return $this->addRoute(['GET', 'HEAD'], $pattern, $action);
	}

	public function put($pattern, $action)
	{
		return $this->addRoute(['PUT'], $pattern, $action);
	}

	public function post($pattern, $action)
	{
		return $this->addRoute(['POST'], $pattern, $action);
	}

	public function delete($pattern, $action)
	{
		return $this->addRoute(['DELETE'], $pattern, $action);
	}

	public function prefixes($prefix, \Closure $closure)
	{
		$newRouter = new static();

		$newRouter->setBasepath($prefix);

		$closure->call($newRouter);

		$this->getCollection()->merge($newRouter->getCollection());

		return $this;
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

	public function processRouteFilters(Route $route)
	{
		$filters = $this->filters->filter(function ($filter) use($route) {
			return $route->hasFilter($filter->getName());
		});

		foreach ($filters as $filter)
		{
			if (null !== $result = $filter($route)) {

				return $result;
			}
		}
	}
}