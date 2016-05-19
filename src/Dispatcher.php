<?php

namespace PHPLegends\Routes;
use PHPLegends\Routes\Exceptions\NotFoundException;
use PHPLegends\Routes\Exceptions\InvalidVerbException;

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
			throw new NotFoundException("Unable to find '{$this->uri}'");
		}

		$route = $routes->findByVerb($this->verbs);

		if ($route === null)
		{
			throw new InvalidVerbException(
				sprintf('Invalid verb for route "%s"', $this->uri)
			);
		}

		$result = $this->getRouter()->processRouteFilters($route);

		if ($result !== null) return $result;

		return call_user_func_array(
			$route->getActionAsCallable(), $route->match($this->uri)
		);

	}
}