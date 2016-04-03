<?php
namespace Ore2\Test;
use Ore2\Session;

class sessionTest extends \PHPUnit_Framework_TestCase
{
    public function testSession1()
    {
        $s = new Session(new Session\Storage\Mock());
        $s['str'] = 'string';
        $this->assertEquals('string', $s['str']);
    }

    public function testPHPSessionWrite()
    {
        $_SESSION = [];
        $s = new Session(new Session\Storage\PHPSession());
        $s['str'] = 'string2';
        $this->assertEquals('string2', $s['str']);
        $this->assertEquals('string2', $_SESSION['str']);
    }

    public function testSessionReload()
    {
        $_SESSION = [];
        $_SESSION['str'] = 'string3';

        $s = new Session(new Session\Storage\PHPSession());
        $this->assertEquals('string3', $s['str']);
        $s['str'] = 'string3-1';
        unset($s);

        $s = new Session(new Session\Storage\PHPSession());
        $this->assertEquals('string3-1', $s['str']);
    }

}
