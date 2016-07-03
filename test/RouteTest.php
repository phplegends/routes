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
		
		$result = $this->route->getResult('home/one/two');

		// check the arguments

		$this->assertEquals(['one', 'two'], $result->getArguments());

		// Invoke the action

		$result->invoke();

		// is valid?

		$this->assertTrue($this->route->isValid('home/hello/batman'));
	}

	public function _routeMethod($one, $two)
	{
		$this->assertEquals('one', $one);

		$this->assertEquals('two', $two);
	}

	public function testAllMethodsOfUriMatchingOfClosure()
	{
		// Testing "optional" route pattern

		$result = $this->routeClosure->getResult('closure/5');

		// check the arguments

		$this->assertEquals(['5'], $result->getArguments());

		// Invoke the action

		$this->assertEquals('A|B = 5|__none__', $result->invoke());

		$result = $this->routeClosure->getResult('closure/5/title-of-post');

		$this->assertEquals(['5', 'title-of-post'], $result->getArguments());

		$this->assertEquals('A|B = 5|title-of-post', $result());

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


}