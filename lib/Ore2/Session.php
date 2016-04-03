<?php
namespace Ore2;

use Ore2\Session\StorageInterface;

class Session implements \ArrayAccess
{
    private $_storage;

    function __construct(StorageInterface $storage)
    {
        $this->_storage = $storage;
    }

    public function __set($key, $something)
    {
        $this->_storage->set($key, $something);
    }

    public function __get($key)
    {
        if(!$this->_storage->isset($key)) return null;
        return $this->_storage->get($key);
    }

    /*
     * implement ArrayAccess methods.
     */
    public function offsetExists($offset)
    {
        return $this->_storage->isset($offset);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        if ($this->_storage->isset($offset)) {
            $this->_storage->unset($offset);
        }
    }
}
