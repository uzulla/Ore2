<?php
declare(strict_types=1);
namespace Ore2\Session\Storage;

use Ore2\Session\StorageInterface;

class PHPSession implements StorageInterface
{
    public function __construct()
    {
        // PSR-7では、PHP純正のセッションをそのままつかうのはダメだと思うけれど時間切れ
        if(session_status()===PHP_SESSION_NONE) session_start();
    }

    public function get($key)
    {
        return $_SESSION[$key];
    }

    public function set($key, $val)
    {
        $_SESSION[$key] = $val;
    }

    public function isset($key)
    {
        return isset($_SESSION[$key]);
    }

    public function unset($key)
    {
        unset($_SESSION[$key]);
    }

    // TODO
    // Regenerate id
    // Destroy session
    //
}
