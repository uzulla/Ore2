<?php
namespace Ore2\Test;
use Ore2\Logger;

class logTest extends \PHPUnit_Framework_TestCase
{
    const dateRegexFormat = '\[[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}\]';

    public function testLog()
    {
        $log_file = __DIR__.'/test.log';
        if(file_exists($log_file)) unlink($log_file);

        $l = new Logger(\Ore2\Logger::DEBUG, $log_file);
        $l->debug('this is debug'); // ignore

        $output = file_get_contents($log_file);
        $this->assertTrue(preg_match('/\A'.$this::dateRegexFormat.' DEBUG: this is debug {}\n\z/u', $output)===1);

        if(file_exists($log_file)) unlink($log_file);
    }

    public function testLogLevel()
    {
        $log_file = __DIR__.'/test.log';
        if(file_exists($log_file)) unlink($log_file);

        $l = new Logger(\Ore2\Logger::ERROR, $log_file);
        $l->debug('this is debug'); // this will ignore
        $l->error('this is error');
        $l->emergency('this is emerg');

        $output = file_get_contents($log_file);
        $this->assertTrue(preg_match(
                '/\A'.
                $this::dateRegexFormat.' ERROR: this is error {}\n'.
                $this::dateRegexFormat.' EMERGENCY: this is emerg {}\n'.
                '\z/u',
                $output)===1);

        if(file_exists($log_file)) unlink($log_file);
    }

    public function testParseContext()
    {
        $log_file = __DIR__.'/test.log';
        if(file_exists($log_file)) unlink($log_file);

        $l = new Logger(\Ore2\Logger::DEBUG, $log_file);
        $l->debug('test', [1=>2, 3=>4]);

        $output = file_get_contents($log_file);
        $this->assertTrue(preg_match('/\A'.$this::dateRegexFormat.' DEBUG: test {"1":"2","3":"4"}\n\z/u', $output)===1);

        if(file_exists($log_file)) unlink($log_file);
    }
}