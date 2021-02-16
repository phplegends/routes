<?php

use PHPLegends\Routes\Router;

class RouterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->router = new Router;

        $this->router->get('/', [RouterTest::class, '_routeMethod']);

        $this->router->get('/dispatcher', function ()
        {
            return 'Test dispatch completeded';
        });

    }

    public function testAddRoute()
    {
        // Internaly call addRoute

        $this->router->get('/home/{str}/{str}', [RouterTest::class, '_routeMethod']);

        $r = $this->router->findByUri('home/one/two');

        $this->assertInstanceOf('PHPLegends\Routes\Route', $r);
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

        ], function ($router) {

            $router->get('create', function () {}, 'creator');

            $router->delete('destroy', function () {}, 'destroyer');

        });

        $route = $this->router->findByUri('in_group/create');

        $this->assertEquals('in_group/create', $route->getPattern());

        $this->assertEquals('in_group.creator', $route->getName());

        $this->assertCount(2, $this->router->getRouteCollection()->filterByPrefixName('in_group'));

    }


    public function testResource()
    {

        // Resgistry method if exists

        $this->router->resource('RoutableResource', 'routable-resource');

        $this->assertCount(
            5,
            $this->router->getRouteCollection()->filterByPrefixName('routable-resource')
        );

        $this->assertInstanceOf(
            'PHPLegends\Routes\Route',
            $this->router->findByUriAndVerb('routable-resource', 'GET')
        );

        $this->assertInstanceOf(
            'PHPLegends\Routes\Route',
            $this->router->findByUriAndVerb('routable-resource', 'POST')
        );

        $this->assertInstanceOf(
            'PHPLegends\Routes\Route',
            $this->router->findByUriAndVerb('routable-resource/1', 'PUT')
        );

        $this->assertInstanceOf(
            'PHPLegends\Routes\Route',
            $this->router->findByUriAndVerb('routable-resource/1', 'DELETE')
        );


        $this->assertInstanceOf(
            'PHPLegends\Routes\Route',
            $this->router->findByUriAndVerb('routable-resource/1', 'GET')
        );


        $this->router->findByUriAndVerb('routable-resource/1', 'GET');

    }


    public function testResourceWithGroup()
    {
        $me = $this;

        $this->router->group([
            'filters'   => ['rr_a', 'rr_b'],
            'prefix'    => 'prefix/'
        ], function ($router) use($me) {

            $router->resource(RoutableResource::class, 'routable-resource');

            $route = $router->findByUriAndVerb('prefix/routable-resource/1', 'GET');

            $me->assertEquals($route->getName(), 'routable-resource.show');

                
        });
    }

}
