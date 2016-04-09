<?php
declare(strict_types=1);
namespace Ore2\Session\Storage;

use Ore2\Session\StorageInterface;

class PHPSession implements StorageInterface
{
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
