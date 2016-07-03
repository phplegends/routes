<?php

use PHPLegends\Routes\Collection;
use PHPLegends\Routes\Route;


class CollectionTest extends PHPUnit_Framework_TestCase
{
	public function test()
	{
		$routes = new Collection();

		$r = new Route('/test/method/{str}/{str}', 'CollectionTest::routeMethod');

		$routes->add($r);

		$this->assertCount(1, $routes);

		$this->assertEquals($r, $routes->findByUri('/test/method/one/two'));

	}

	public function routeMethod($one, $two) {
	}


	public function testMap()
	{
		$routes = new Collection();

		$routes->add(new Route('a', function () {}));
		$routes->add(new Route('b', function () {}));
		$routes->add(new Route('c', function () {}));

		$patterns = $routes->map(function ($r) {
			return $r->getPattern();
		});

		$this->assertEquals($patterns->all(), ['a', 'b', 'c']);
	}
}