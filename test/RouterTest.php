<?php


use PHPLegends\Routes\Router;


class RouterTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->router = new Router;

		$this->router->get('/', 'RouterTest::_routeMethod')->setFilters(['before.age']);

		$this->router->get('/dispatcher', function ()
		{
			return 'Test dispatch completeded';
		});

		$this->router->addFilter('before.age', function ($route, $age = null)
		{	
			if ($age < 18) return 'Age not accepted';
		});
	}

	public function testAddRoute()
	{	
		// Internaly call addRoute

		$this->router->get('/home/{str}/{str}', 'RouterTest::_routeMethod');

		$r = $this->router->findByUri('home/one/two');

		$this->assertInstanceOf('PHPLegends\Routes\Route', $r);
	}

	public function testFilter()
	{
		$this->router->addFilter('before.auth', function ($route, $auth = false)
		{
			if ($auth === false)
			{
				return 'Loggin, please';
			}
		});	

		$this->assertInstanceOf(
			'PHPLegends\Routes\FilterCollection', $this->router->getFilters()
		);

		$this->assertCount(2, $this->router->getFilters());

	}

	public function testCallFilters()
	{
		$routeIndex = $this->router->findByUri('/');

		$result[0] = $this->router->getFilters()->processRouteFilters($routeIndex, 15);

		$result[1] = $this->router->getFilters()->processRouteFilters($routeIndex, 18);

		$this->assertNotNull($result[0]);

		$this->assertNull($result[1]);

		$this->assertEquals('Age not accepted', $result[0]);
	}

	public function testDispatcher()
	{	
		
		$dispatcher = new PHPLegends\Routes\Dispatcher('dispatcher', 'GET');

		$response = $this->router->dispatch($dispatcher);

		$this->assertEquals('Test dispatch completeded', $response);

	}

	public function testInvalidVerbExceptionInDispatcher()
	{

		$dispatcher = new PHPLegends\Routes\Dispatcher('dispatcher', 'POST');

		try {

			$response = $this->router->dispatch($dispatcher);

		} catch (\Exception $e) {

			$this->assertInstanceOf('PHPLegends\Routes\Exceptions\InvalidVerbException', $e);

		}

	}

	public function testNotfoundExceptionInDispatcher()
	{

		$dispatcher = new PHPLegends\Routes\Dispatcher('non-exists-route', 'GET');

		try {

			$response = $this->router->dispatch($dispatcher);

		} catch (\Exception $e) {

			$this->assertInstanceOf('PHPLegends\Routes\Exceptions\NotFoundException', $e);

		}
	}

	public function _routeMethod($one = null, $two = null)
	{
		return 'You are called RouteTest::_routeMethod()';
	}



	public function testGroup()
	{
		$this->router->group([
			'name'   => 'in_group.',
			'prefix' => 'in_group/',
			'filters' => ['a', 'b']

		], function () {

			$this->get('create', function () {}, 'creator')->addFilter('c');

			$this->delete('destroy', function () {}, 'destroyer');

		});

		$route = $this->router->findByUri('in_group/create');

		$this->assertEquals('in_group/create', $route->getPattern());

		$this->assertEquals('in_group.creator', $route->getName());

		$this->assertEquals(['a', 'b', 'c'], $route->getFilters());
	}
}