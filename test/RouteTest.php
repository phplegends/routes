<?php

use PHPLegends\Routes\Route;


class RouteTest extends PHPUnit_Framework_TestCase
{

	public function test()
	{
		$r = new Route('/home/{str}/{str}', 'RouteTest::_routeMethod', ['GET', 'HEAD']);

		$this->assertEquals(
			['one', 'two'],
			$r->match('home/one/two')
		);

		call_user_func_array($r->getAction(), $r->match('home/one/two'));
	}

	public function _routeMethod($one, $two)
	{
		$this->assertEquals('one', $one);

		$this->assertEquals('two', $two);
	}

	public function testClosure()
	{
		$r = new Route('/home/{num}', function ($id)
		{
			return (int) $id;
		});

		$this->assertFalse($r->match('home/string'));

		$this->assertEquals(['5'], $r->match('home/5'));

		$response = call_user_func_array($r->getAction(), $r->match('home/4'));

		$this->assertEquals(4, $response);
	}
	
}