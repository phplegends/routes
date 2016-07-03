<?php

namespace PHPLegends\Routes;

use PHPLegends\Collections\ListCollection;

/**
 * Collection of Routes
 * 
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
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

	/**
	 * @param Route $route
	 * @throws |UnexpectedValueException if non Route instance passed
	 * 
	 * */
	public function add($route)
	{
		if (! $route instanceof Route)
		{
			throw new \UnexpectedValueException('Only Route can be added');
		}

		return parent::add($route);
	}

	/**
	 * 
	 * @param string|array $verb
	 * @return \PHPlegends\Route\Route | null
	*/
	public function findByVerb($verb)
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

	protected function getVerbFilter($verb)
	{
		return function ($route) use ($verb) {

			return $route->acceptedVerb($verb);

		};
	}

	protected function getUriFilter($uri)
	{
		return function ($route) use ($uri) {

			return $route->isValid($uri);
		};
	}

	/**
	 * 
	 * @param callable $callback
	 * 
	 * @return Listcollection
	 * */
	public function map(callable $callback = null)
	{	
		$items = array_map(
            $callback,
            $this->all(),
            $keys = $this->keys()
        );

        return new ListCollection(array_combine($keys, $items));
	}
}