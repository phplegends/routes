<?php

use PHPLegends\Routes\Result;


class ResultTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->result = new Result(function ($a, $b, $c) {

            return sprintf('Hello %s-%s-%s', $a, $b, $c);

        }, ['a', 'b', 'c']);
    }

    public function testArguments()
    {
        $this->assertEquals(['a', 'b', 'c'], $this->result->getArguments());
    }

    public function testIsClosure()
    {
        $this->assertTrue($this->result->isClosure());
    }

    public function testInvokeAnd__Invoke()
    {
        $this->assertEquals('Hello a-b-c', $this->result->invoke());

        $this->assertEquals('Hello a-b-c', call_user_func($this->result));
    }
    
}
