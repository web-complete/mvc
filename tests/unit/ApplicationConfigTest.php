<?php

namespace tests\unit;

use tests\MvcTestCase;
use WebComplete\mvc\ApplicationConfig;

class ApplicationConfigTest extends MvcTestCase
{

    public function testExists()
    {
        $config = new ApplicationConfig(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertTrue(isset($config['a']));
        $this->assertFalse(isset($config['d']));
    }

    public function testGetSetUnset()
    {
        $config = new ApplicationConfig(['a' => 1, 'b' => 2, 'c' => 3]);
        $config['c'] = 4;
        $config['d'] = 5;
        unset($config['a']);
        $this->assertEquals(['b' => 2, 'c' => 4, 'd' => 5], $config->getData());
    }

    public function testInnerArray()
    {
        $config = new ApplicationConfig(['a' => ['b' => 3]]);
        $a = $config['a'];
        $a['b'] = 4;
        $config['a'] = $a;
        $this->assertEquals(['a' => ['b' => 4]], $config->getData());
    }
}
