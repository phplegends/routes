<?php

use PHPLegends\Routes\RoutableInspector;

class RoutableInspectorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->inspector = new RoutableInspector('Routes\RoutableTarget');
    }

    public function testGetClass()
    {
        $this->assertEquals('Routes\RoutableTarget', $this->inspector->getClass());
    }

    public function testGetReflection()
    {
        $this->assertInstanceOf('\ReflectionClass', $this->inspector->getReflection());
    }

    public function testGetRoutables()
    {
        $routables = $this->inspector->generateRoutables();

        $this->assertCount(9, $routables->getCollection());

        $this->assertInstanceOf(
            'PHPLegends\Routes\Route',
            $routables->findByUriAndVerb('routable-target/', 'GET')
        );

        $this->assertInstanceOf(
            'PHPLegends\Routes\Route',
            $routables->findByUriAndVerb('routable-target/page-contact', 'GET')
        );
    }
}


