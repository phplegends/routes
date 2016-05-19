<?php

namespace PHPLegends\Routes;

abstract class AbstractDispatcher implements Dispatchable
{
	protected $router;

	public function setRouter(Router $router)
	{
		$this->router = $router;
	}

	public function getRouter()
	{
		return $this->router;
	}

	public function handleException(\Exception $e)
	{
		throw $e;
	}


}