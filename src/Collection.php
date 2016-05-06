<?php

namespace PHPLegends\Routes;

use PHPLegends\Collections\ListCollection;

/**
 * Coleção de rotas
 * */
class Collection extends ListCollection
{
	/**
	 * @var array
	 * */
	protected $items = [];

	/**
	* @param \WallaceMaxters\SevenFramework\Routing\Route $route
	* @return \WallaceMaxters\SevenFramework\Routing
	*/
	public function attach(Route $route)
	{
		$this->add($route);

		return $this;
	}

	public function add($route)
	{
		if (! $route instanceof Route)
		{
			throw new \UnexpectedValueException('Only Route can be added');
		}

		parent::add($route);
	}

	/**
	* @param string $pattern
	* @return \WallaceMaxters\SevenFramework\Routing\Route | null
	*/
	public function find($pattern)
	{

		foreach ($this->items as $route) {

			if ($route->match($pattern)) {

				return $route;
			}
		}
	}
}