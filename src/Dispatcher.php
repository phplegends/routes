<?php

namespace PHPLegends\Routes;
use PHPLegends\Routes\Exceptions\NotFoundException;
use PHPLegends\Routes\Exceptions\InvalidVerbException;

class Dispatcher implements Dispatchable
{	

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
	 * @param array|string $verb
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
		$routes = $router->getCollection()->filterByUri($this->uri);

		if ($routes->isEmpty()) {
			
			throw new NotFoundException("Unable to find '{$this->uri}'");
		}

		$route = $routes->findByVerb($this->verb);

		if ($route === null) {

			throw new InvalidVerbException(
				sprintf('Invalid verb for route "%s"', $this->uri)
			);
		}

		$filterResult = $router->getFilters()->processRouteFilters($route);

		if ($filterResult !== null) return $filterResult;

		return call_user_func_array(
			$route->getActionAsCallable(), $route->match($this->uri)
		);

	}
}