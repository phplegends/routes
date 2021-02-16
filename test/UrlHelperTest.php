<?php

use PHPLegends\Routes\UrlHelper;
use PHPLegends\Routes\Router;

class UrlHelperTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->router = new Router;

        $fn = function () {
            return 'Ok, baby';
        };

        $this->router->get('/', $fn)->setName('home');

        $this->router->get('/user/{num}', $fn)->setName('user.show');

        $this->router->get('/pages/{num?}', ['Routes\RoutableTarget', 'actionIndexGet'])->setName('pages');

        $this->router->get('/post/{num}/{str?}', ['Routable1', 'actionLoginGet'])->setName('post.show');

        $this->url = new UrlHelper($this->router->getRouteCollection(), 'http://phplegends.io/');
    }

    public function testGetBaseUrl()
    {
        $this->assertEquals('http://phplegends.io/', $this->url->getBaseUrl());
    }

    public function testTo()
    {        
        $this->assertEquals('http://phplegends.io/', $this->url->to('/'));

        $this->assertEquals('http://phplegends.io/pages/', $this->url->to('/pages'));

        $this->assertEquals(
            'http://phplegends.io/pages/?name=Wallace',
            $this->url->to('/pages', ['name' => 'Wallace'])
        );

        $this->assertEquals(
            'http://phplegends.io/',
            $this->url->to('')
        );
    }


    public function testRoute()
    {
        $this->assertEquals(
            'http://phplegends.io/',
            $this->url->route('home')
        );

        $this->assertEquals(
            'http://phplegends.io/post/25/hello-world/',
            $this->url->route('post.show', [25, 'hello-world'])
        );

        $this->assertEquals(
            'http://phplegends.io/user/2/',
            $this->url->route('user.show', 2)
        );


        $this->assertEquals(
            'http://phplegends.io/pages/2/?order_by=name',
            $this->url->route('pages', 2, ['order_by' => 'name'])
        );
    }


    public function testSecure()
    {
        $this->assertEquals('https://phplegends.io/', $this->url->secure()->to('/'));
    }

}