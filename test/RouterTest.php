<?php


use PHPLegends\Routes\Router;


class RouterTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->router = new Router;
	}

	public function test()
	{
	
		$this->router->get('/', 'RouterTest::_routeMethod');

		$this->router->get('/home/{str}/{str}', 'RouterTest::_routeMethod');

		$r = $this->router->findByUri('home/one/two');

		$this->assertInstanceOf('PHPLegends\Routes\Route', $r);
	}

	public function testAddFilter()
	{
		$this->router->addFilter('before.auth', function ($query)
		{
			if ($query['idade'] < 18)
			{
				return 'Você é menor de idade';
			}
		});
	}

	public function _routeMethod($one = null, $two = null)
	{

	}
}