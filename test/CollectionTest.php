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

		$this->assertEquals($r, $routes->find('/test/method/one/two'));

	}

	public function routeMethod($one, $two) {
	}
}