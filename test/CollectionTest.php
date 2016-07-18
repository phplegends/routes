<?php

use PHPLegends\Routes\Collection;
use PHPLegends\Routes\Route;


class CollectionTest extends PHPUnit_Framework_TestCase
{
	public function testAddAndCount()
	{
		$routes = new Collection();

		$route = new Route('/test/method/{str}/{str}', 'CollectionTest::routeMethod');

		$routes->add($route);

		$this->assertCount(1, $routes);

		$this->assertEquals($route, $routes->findByUri('/test/method/one/two'));

	}

	public function routeMethod($one, $two) 
	{
		
	}

	public function testMap()
	{
		$routes = new Collection();

		$routes->add(new Route('a', function () {

			return 'A';

		}));

		$routes->add(new Route('b', function () {

			return 'B';

		}));

		$routes->attach(new Route('c', function () {

			return 'C';
			
		}));

		$patterns = $routes->map(function (Route $route) {

			return $route->getPattern();
		});

		$this->assertEquals($patterns->all(), ['a', 'b', 'c']);
	}


}