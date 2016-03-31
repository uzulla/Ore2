<?php
namespace Ore2\Test;
use Ore2\Template;

class renderTest extends \PHPUnit_Framework_TestCase
{
    public function testRender1()
    {
        $t = new Template([
            'template_dir' => __DIR__
        ]);
        $param = ['name'=>'uzulla', 'list'=>[1,2,3]];

        $code = $t->parse('testRender1.twig');
        $this->assertEquals($code, file_get_contents(__DIR__.'/testRender1.php'));

        $html = $t->execute($code, $param);
        $this->assertEquals($html, file_get_contents(__DIR__.'/testRender1.html'));

        $html2 = $t->render('testRender1.twig', ['name'=>'uzulla', 'list'=>[1,2,3]]);
        $this->assertEquals($html2, file_get_contents(__DIR__.'/testRender1.html'));
    }
}