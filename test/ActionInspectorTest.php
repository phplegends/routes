<?php

use PHPLegends\Routes\ActionInspector;

class ActionInspectorTest extends PHPUnit_Framework_TestCase
{
    public function testStaticActions()
    {
        $this->assertEquals(
            ActionInspector::TYPE_CLOSURE,
            ActionInspector::getType(function () {})
        );

        $this->assertEquals(
            ActionInspector::TYPE_STATIC_METHOD,
            ActionInspector::getType('ActionInspectorTest::actionStaticMethod')
        );


        $this->assertEquals(
            ActionInspector::TYPE_DINAMIC_METHOD,
            ActionInspector::getType('ActionInspectorTest::actionDynamicMethod')
        );


        $this->assertEquals(
            ActionInspector::TYPE_FUNCTION,
            ActionInspector::getType('var_dump')
        );
    }


    public static function actionStaticMethod() {}

    public function actionDynamicMethod() {}
}