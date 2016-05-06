<?php


use PHPLegends\Routes\Router;


class RouterTest extends PHPUnit_Framework_TestCase
{
	public function test()
	{
		$router = new Router;

		$router->get('/', 'RouterTest::_routeMethod');

		$router->get('/home/{str}/{str}', 'RouterTest::_routeMethod');

		$r = $router->findByUri('home/one/two');

		$this->assertInstanceOf('PHPLegends\Routes\Route', $r);
	}

	public function _routeMethod($one = null, $two = null)
	{

	}
}