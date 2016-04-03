<?php
namespace Ore2;

use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{

    protected $log_file_name = null;
    protected $level = 100; // level 指定無しではDEBUGになる

    const DEBUG = 100;
    const INFO = 200;
    const NOTICE = 250;
    const WARNING = 300;
    const ERROR = 400;
    const CRITICAL = 500;
    const ALERT = 550;
    const EMERGENCY = 600;

    protected static $levels = array(
        100 => 'DEBUG',
        200 => 'INFO',
        250 => 'NOTICE',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL',
        550 => 'ALERT',
        600 => 'EMERGENCY',
    );

    /**
     * @param int $level log level
     * @param string $file_name log file name
     */
    public function __construct($level = null, $file_name = null)
    {
        if (!is_null($file_name))
            $this->log_file_name = $file_name;

        if (!is_null($level))
            $this->level = $level;
    }

    public function debug($message, array $context = array())
    {
        $this->log('DEBUG', $message, $context);
    }

    public function info($message, array $context = array())
    {
        $this->log('INFO', $message, $context);
    }

    public function notice($message, array $context = array())
    {
        $this->log('NOTICE', $message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->log('WARNING', $message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->log('ERROR', $message, $context);
    }

    public function critical($message, array $context = array())
    {
        $this->log('CRITICAL', $message, $context);
    }

    public function alert($message, array $context = array())
    {
        $this->log('ALERT', $message, $context);
    }

    public function emergency($message, array $context = array())
    {
        $this->log('EMERGENCY', $message, $context);
    }

    public function log($level, $message, array $context = array())
    {
        if (!preg_match('/\A[0-9]{3}\z/u', $level)) $level = constant("static::" . $level);
        if (!isset(static::$levels[$level])) throw new \InvalidArgumentException('Unknown log level');
        if ($this->level > $level) return;

        $level_str = static::$levels[$level];

        // format context;
        $context_str = '{';
        foreach ($context as $k => $v) {
            $context_str .= "\"$k\":\"$v\",";
        }
        $context_str = rtrim($context_str, ',');
        $context_str .= '}';

        $log_line = "{$level_str}: {$message} {$context_str}";

        // remove nullbyte char. error_log() can not handle \0.
        $log_line = str_replace("\0", "", $log_line);

        if (is_null($this->log_file_name)) {
            error_log($log_line);
        } else {
            $fh = fopen($this->log_file_name, 'a');
            if (!is_resource($fh)) throw new \UnexpectedValueException(sprintf('"%s" could not be opened.', $this->log_file_name));
            $retry = 5;
            while ($retry--) {
                if (flock($fh, LOCK_EX)) {  // 排他ロック
                    fwrite($fh, date('[Y-m-d H:i:s] ') . $log_line . PHP_EOL);
                    fflush($fh);
                    flock($fh, LOCK_UN);
                    break;
                } else {
                    usleep(rand(1, 10) * 100000); // retry wait 0.1〜1秒
                }
            }
            if ($retry === 0) error_log(get_class($this) . ": Can't open logfile. " . $log_line);
            fclose($fh);
        }
    }

}