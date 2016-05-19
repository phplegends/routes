<?php

namespace PHPLegends\Routes;

use PHPLegends\Collections\Collection as BaseCollection;

class FilterCollection extends BaseCollection
{
	public function add($filter)
	{
		if (! $filter instanceof Filter) {

			throw new \InvalidArgumentException(
				'Argument expected filter'
			);
		}

		return parent::add($filter);
	}

	public function findByPrefix($name)
	{
		return $this->first(function ($filter) use($name)
		{
			return strpos($filter->getName(), $name) === 0;
		});
	}
}

