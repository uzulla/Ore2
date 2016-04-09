<?php
declare(strict_types=1);
namespace Ore2\Session;

interface StorageInterface
{
    public function get($key);

    public function set($key, $val);

    public function isset($key);

    public function unset($key);
}
