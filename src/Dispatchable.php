<?php

namespace PHPLegends\Routes;

/**
 * 
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
 * */
interface Dispatchable
{
	/**
     *  Dispatcher for router
	 * 
	 * @param PHPLegends\Routes\Router
	 * */
	public function dispatch(Router $router);
}