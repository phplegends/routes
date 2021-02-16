<?php

use PHPLegends\Routes\RouteCollection;
use PHPLegends\Routes\Route;


class RouteCollectionTest extends PHPUnit_Framework_TestCase
{
	public function testattachAndCount()
	{
		$routes = new RouteCollection();

		$route = new Route('/test/method/{str}/{str}', [Fake::class, 'method']);

		$routes->attach($route);

		$this->assertCount(1, $routes);

		$this->assertEquals($route, $routes->findByUri('/test/method/one/two'));

	}

	public function testMap()
	{
		$routes = new RouteCollection();

		$routes->attach(new Route('a', function () {

			return 'A';

		}));

		$routes->attach(new Route('b', function () {

			return 'B';

		}));

		$routes->attach(new Route('c', function () {

			return 'C';
			
		}));

		$patterns = $routes->map(function (Route $route) {
			return $route->getPattern();
		});

		$this->assertEquals($patterns, ['a', 'b', 'c']);
	}


}