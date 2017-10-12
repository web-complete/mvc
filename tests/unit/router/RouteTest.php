<?php

namespace tests\unit\router;

use tests\MvcTestCase;
use WebComplete\mvc\router\Route;

class RouteTest extends MvcTestCase
{

    public function testSetters()
    {
        $route = new Route('class1', 'method1', ['a']);
        $route->setClass('class2');
        $route->setMethod('method2');
        $route->setParams(['b']);
        $this->assertEquals('class2', $route->getClass());
        $this->assertEquals('method2', $route->getMethod());
        $this->assertEquals(['b'], $route->getParams());
    }
}
