<?php

namespace  PHPLegends\Routes;

class Router
{
	/**
	 * @var string
	 *
	 * */
	protected $basePath = '';	

	/**
	 * @var \WallaceMaxters\SevenFramework\Routing\RouteCollection
	 *
	 * */

	protected $routes;

	/**
	 * @var array
	 * */
	protected $filters = [];


	public function __construct(Collection $routes = null)
	{	
		$this->routes = $routes ?: new Collection;
	}

	public function setBasepath($basePath)
	{
		$this->basePath = rtrim($basePath, '/');
	}

	public function findByUri($uri)
	{	
		return $this->routes->first(function ($route) use($uri) {
			return $route->match($uri) !== false;
		});

	}

	public function dispatch($uri, $method = '*', \Closure $closure = null)
	{
		$route = $this->findByUri($uri);

		if (! $route) return false;

		$route->acceptedVerbs($method);

		$closure && $closure ($route);

		return call_user_func_array($route->getAction(), $route->match($uri));
		
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

	public function addRoute(array $methods, $pattern, $action)
	{

		$pattern = trim($this->basePath . '/' . $pattern, '/');

		$newRoute = new Route($pattern, $action);

		$newRoute->setMethod($methods);

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

}