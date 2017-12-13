<?php

namespace tests\unit\router;

use tests\MvcTestCase;
use WebComplete\mvc\router\Routes;

class RoutesTest extends MvcTestCase
{

    public function testRoutes()
    {
        $routes = new Routes(['aaa', 'bbb', 'ccc']);
        $this->assertTrue(isset($routes[0]));
        $routes[2] = 'ddd';
        $routes[3] = 'eee';
        $this->assertEquals('ddd', $routes[2]);
        $this->assertEquals('eee', $routes[3]);
        unset($routes[3]);
        $this->assertFalse(isset($routes[3]));
        $result = [];
        foreach ($routes as $k => $route) {
            $result[$k] = $route;
        }
        $this->assertEquals(['aaa', 'bbb', 'ddd'], $result);
    }

    public function testRoutesAdd()
    {
        $routes = new Routes([
            ['GET', 'aaa'],
            ['GET', 'bbb'],
            ['GET', 'ccc'],
        ]);
        $routes->addRoute(['GET', 'xxx']);
        $this->assertEquals([
            ['GET', 'xxx'],
            ['GET', 'aaa'],
            ['GET', 'bbb'],
            ['GET', 'ccc'],
        ], $routes->getData());
        $routes->addRoute(['GET', 'zzz'], 'ccc');
        $this->assertEquals([
            ['GET', 'xxx'],
            ['GET', 'aaa'],
            ['GET', 'bbb'],
            ['GET', 'zzz'],
            ['GET', 'ccc'],
        ], $routes->getData());
    }
}
