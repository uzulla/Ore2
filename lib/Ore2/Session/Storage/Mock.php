<?php
namespace Ore2\Session\Storage;

use Ore2\Session\StorageInterface;

class Mock implements StorageInterface
{
    private $store = [];

    public function get($key){
        return $this->store[$key];
    }

    public function set($key, $val){
        $this->store[$key] = $val;
    }

    function isset($key){
        return isset($this->store[$key]);
    }

    public function unset($key){
        unset($this->store[$key]);
    }
}
