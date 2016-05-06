<?php

namespace PHPLegends\Routes;

use PHPLegends\Routes\Exceptions\HttpException;

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

	public function dispatch($uri, $verb, \Closure $closure = null)
	{
		$uri = strtok($uri, '?');

		$route = $this->findByUri($uri);

		if (! $route) {

			throw new HttpException("Route not found", 404);
		}

		$route->validateVerb($verb);

		$closure && $closure ($route);

		return call_user_func_array($route->getActionAsCallable(), $route->match($uri));
		
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

}