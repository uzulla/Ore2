<?php
namespace Ore2\Test;

use Ore2\Container;

class containerTest extends \PHPUnit_Framework_TestCase
{
    const dateRegexFormat = '\[[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}\]';

    public function testContainerWithMagic()
    {
        $c = new Container();

        $c->str = "string";

        $this->assertEquals("string", $c->str);

        $c->factory = function () {
            static $a = 0;
            $a++;
            return $a;
        };

        $this->assertEquals("1", $c->factory);
        $this->assertEquals("2", $c->factory);
        $this->assertEquals("3", $c->factory);

        $c->date = new \DateTime();
        $this->assertInstanceOf('\DateTime', $c->date);
    }

    public function testContainerWithArray()
    {
        $c = new Container();

        $c['str'] = "string";

        $this->assertEquals("string", $c['str']);

        $this->assertTrue(isset($c['str']));
        unset($c['str']);
        $this->assertFalse(isset($c['str']));

        $c['factory'] = function () {
            static $a = 0;
            $a++;
            return $a;
        };

        $this->assertEquals("1", $c['factory']);
        $this->assertEquals("2", $c['factory']);
        $this->assertEquals("3", $c['factory']);

        $c['date'] = new \DateTime();
        $this->assertInstanceOf('\DateTime', $c['date']);
    }

}
