<?php

use PHPLegends\Routes\Route;


class RouteTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		$this->route = new Route('/home/{str}/{str}', 'RouteTest::_routeMethod', ['GET', 'HEAD']);

		$this->route->setName('controller.route');

		$this->route->addFilter('a', 'b', 'c');

		$this->routeClosure = new Route('closure/{num}/{str?}', function ($a, $b = '__none__')
		{
			return "A|B = $a|$b";

		}, ['POST']);


		$this->routeClosure->addFilter(['x', 'y', 'z']);
	}

	public function testAllMethodsOfUriMatching()
	{

		$result = $this->route->match('home/one/two');

		// check the arguments

		$this->assertEquals(['one', 'two'], $this->route->getParameters());
	}

	public function _routeMethod($one, $two)
	{
		$this->assertEquals('one', $one);

		$this->assertEquals('two', $two);
	}

	public function testAllMethodsOfUriMatchingOfClosure()
	{
		// Testing "optional" route pattern

		$this->routeClosure->match('closure/5');

		// check the arguments

		$this->assertEquals(['5'], $this->routeClosure->getParameters());

		// Invoke the action

		//$this->assertEquals('A|B = 5|__none__', $result->invoke());

		$this->routeClosure->match('closure/5/title-of-post');

		$this->assertEquals(['5', 'title-of-post'], $this->routeClosure->getParameters());

		//$this->assertEquals('A|B = 5|title-of-post', $result());

	}


	public function testGetName()
	{
		$this->assertEquals('controller.route', $this->route->getName());

		$this->assertEquals(null, $this->routeClosure->getName());
	}


	public function testGetFilters()
	{
		$this->assertEquals(['a', 'b', 'c'], $this->route->getFilters());

		$this->assertEquals(['x', 'y', 'z'], $this->routeClosure->getFilters());
	}

	public function testGetVerbs()
	{
		$route = new Route('/', function () {});

		$this->assertEquals(['*'], $route->getVerbs());

		$this->assertEquals(['GET', 'HEAD'], $this->route->getVerbs());

		$this->assertEquals(['POST'], $this->routeClosure->getVerbs());
	}

	public function testAddFilters()
	{
		$route = new Route('/', function () {});

		$route->addFilter('before.auth', 'before.age');

		$route->addFilter(['x', 'y']);

		$this->assertEquals(['before.auth', 'before.age', 'x', 'y'], $route->getFilters());
	}

	public function testHasFilter()
	{
		$route = new Route('/', function () {});

		$this->assertFalse($route->hasFilter('before.age'));

		$route->addFilter('before.age');

		$this->assertTrue($route->hasFilter('before.age'));

	}

	public function testToUri()
	{
		$fn = function() {};

		$r1 = new Route('/home/{str}/{num}', $fn);

		$r2 = new Route('/home/{str}/{num?}', $fn);

		$r3 = new Route('/home/{str}/{str}', $fn);

		$r4 = new Route('/home/', $fn);

		$r5 = new Route('/home/{str?}', $fn);

		$r6 = new Route('/home/{date?}', $fn);

		$r7 = new Route('/home/{date}', $fn);

		$this->assertEquals(
			'/home/segment-string/3',
			$r1->toUri(['segment-string', '3'])
		);

		$this->assertEquals('/home/2', $r2->toUri([2]));

		$this->assertEquals('/home/cat/4', $r2->toUri(['cat', 4]));

		$this->assertEquals('/home/2/1', $r3->toUri([2, 1]));

		$this->assertEquals('/home', $r4->toUri([2, 1]));

		$this->assertEquals('/home', $r5->toUri([null]));

		$this->assertEquals('/home', $r5->toUri());

		$this->assertEquals('/home/contact', $r5->toUri(['contact']));

		$this->assertEquals('/home', $r6->toUri());

		$this->assertEquals('/home/2016/07/02', $r6->toUri(['2016/07/02']));

		try {

			$this->assertEquals('/home', $r7->toUri());

		} catch (\Exception $e) {

			$this->assertInstanceOf('UnexpectedValueException', $e);
		}	
	}
	
	public function testBarraInvertida()
	{
		$routes[1] = new Route('test/', function () {
			return 'Com barra no final';
		});

		$routes[2] = new Route('test', function () {
			return 'sem barra no final';
		});

		$routes[3] = new Route('/test', function () {
			return 'sem barra no final';
		});

		$routes[4] = new Route('/test/', function () {
			return 'sem barra no final';
		});

		foreach ($routes as $route) {

			// Pelo amor de Deus, os tres tem que funcionar!

			$this->assertTrue($route->match('test'));

			$this->assertTrue($route->match('test/'));

			$this->assertTrue($route->match('/test/'));

		}
	}

}
