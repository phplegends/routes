<?php

namespace PHPLegends\Routes;

use PHPLegends\Collections\Collection as BaseCollection;

class FilterCollection extends BaseCollection
{
    /**
     * 
     * 
     * */
	public function add($filter)
	{
		if (! $filter instanceof Filter) {

			throw new \InvalidArgumentException(
				'Argument expected filter'
			);
		}

		return parent::add($filter);
	}

    /**
     *  
     * @param string $name
     * @return FilterCollection
     * 
     * */
	public function findByPrefix($name)
	{
		return $this->first(function ($filter) use($name)
		{
			return strpos($filter->getName(), $name) === 0;
		});
	}

	/**
	 * 
	 * @param Route $route
	 * @return null | mixed
	 * */

	public function filterByRoute(Route $route)
	{
		return $this->filter(function ($filter) use($route) {
			return $route->hasFilter($filter->getName());
		});
	}

	/**
	 * Call every filter by route. If result is not null, the result is returned and loop * break
	 * 
	 * @param Route $route
	 * @return null | mixed
	 * */
	public function processRouteFilters(Route $route)
	{
		foreach ($this->filterByRoute($route) as $filter) {

			$result = call_user_func_array($filter, func_get_args());
			
			if ($result !== null) {

				return $result;
			}
		}
	}

    /**
     * 
     * @return \PHPLegends\Collections\Collection
     * */
    public function toBase()
    {
        return new Collection($this->all());
    }
}

