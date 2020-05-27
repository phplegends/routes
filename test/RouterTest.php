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

            $this->assertInstanceOf('\PHPLegends\Routes\Exceptions\InvalidVerbException', $e);

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

        $this->assertCount(2, $this->router->getCollection()->filterByPrefixName('in_group'));

    }

    public function testScaffold()
    {
        $this->router->scaffold('Routable1', 'routable-class');

        $this->assertInstanceOf(
            'PHPLegends\Routes\Route',
            $this->router->findByUriAndVerb('routable-class/login', 'GET')
        );

        $this->assertInstanceOf(
            'PHPLegends\Routes\Route',
            $this->router->findByUriAndVerb('routable-class/login', 'POST')
        );

        $this->assertNull(
            $this->router->findByUriAndVerb('routable-class/login', 'PUT')
        );

        $this->router->scaffold('Routable1', 'routable-controller');

        $this->assertInstanceOf(
            'PHPLegends\Routes\Route',
            $this->router->findByUriAndVerb('routable-controller/login', 'GET')
        );

        $this->assertInstanceOf(
            'PHPLegends\Routes\Route',
            $this->router->findByUriAndVerb('routable-class/test-arguments/one/two', 'GET')
        );

    }

    public function testResource()
    {

        // Resgistry method if exists

        $this->router->resource('RoutableResource', 'routable-resource');

        $this->assertCount(
            5,
            $this->router->getCollection()->filterByPrefixName('routable-resource')
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
            'namespace' => 'Routes',
            'filters'   => ['rr_a', 'rr_b'],
            'prefix'    => 'prefix/'
        ], function ($router) use($me) {

            $router->resource('RoutableResource');

            $me->assertEquals($router->getNamespace(), 'Routes');

            $me->assertEquals($router->getDefaultFilters(), ['rr_a', 'rr_b']);

            $route = $router->findByUriAndVerb('prefix/routable-resource/1', 'GET');

            $me->assertEquals($route->getName(), 'routable-resource.show');

            $me->assertEquals($route->getFilters(), ['rr_a', 'rr_b']);

                
        });
    }

    public function testGroupRoutableClassNameResolution()
    {
        $this->router->group([
            'namespace' => 'Routes',
            'filters'   => ['rr_y', 'rr_x'],
            'prefix'    => 'prefix/'

        ], function ($router)
        {
            $router->scaffold('RoutableTarget', 'r-routable-target');
        });

        $route = $this->router->findByUriAndVerb('prefix/r-routable-target/page-contact', 'GET');

        $this->assertEquals(['GET', 'HEAD'], $route->getVerbs());
    }

}
