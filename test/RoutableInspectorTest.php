<?php

use PHPLegends\Routes\RoutableInspector;

class RoutableInspectorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->inspector = new RoutableInspector('RoutableTarget');
    }

    public function testGetClass()
    {
        $this->assertEquals('RoutableTarget', $this->inspector->getClass());
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


class RoutableTarget
{
    public function actionIndexAny() {}
    public function actionIndexDelete() {}
    public function actionIndexGet() {}
    public function actionIndexHead() {}
    public function actionIndexOptions() {}
    public function actionIndexPost() {}
    public function actionIndexPut() {}
    public function actionPageContactGet() {}
    public function actionIndexTrace() {}

    public function invalidMethod() {}

    public function anotherInvalidRoutableMethod() {}

}

