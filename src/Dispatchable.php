<?php

namespace PHPLegends\Routes;

interface Dispatchable
{
	/**
	 * 
	 * @param PHPLegends\Routes\Router
	 * */
	public function dispatch(Router $router);
}