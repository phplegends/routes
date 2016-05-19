<?php

namespace PHPLegends\Routes;
use PHPLegends\Routes\Exceptions\RouteException;

class Dispatcher extends AbstractDispatcher
{
	public function __construct($uri, $verbs)
	{
		$this->uri = $uri;
		$this->verbs = $verbs;
	}

	public function dispatch()
	{
		$routes = $this->getRouter()->getCollection()->filterByUri($this->uri);

		if ($routes->isEmpty())
		{
			throw new RouteException("Unable to find '{$this->uri}'");
		}

		$route = $routes->findByVerb($this->verbs);

		if ($route === null)
		{
			throw new RouteException('Unable to find');
		}

		$result = $this->getRouter()->processRouteFilters($route);

		if ($result !== null) return $result;

		return call_user_func_array(
			$route->getActionAsCallable(), $route->match($this->uri)
		);

	}
}