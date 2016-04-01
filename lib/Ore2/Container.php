<?php
namespace Ore2;

class Container implements \ArrayAccess
{
    public $list = [];

    public function __set($key, $something)
    {
        $this->list[$key] = $something;
    }

    public function __get($key)
    {
        if (!isset($this->list[$key])) return null;
        if ($this->list[$key] instanceof \Closure) return $this->list[$key]();
        return $this->list[$key];
    }

    /*
     * implement ArrayAccess methods.
     */
    public function offsetExists($offset)
    {
        return isset($this->list[$offset]);
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
        if (isset($this->list[$offset])) {
            unset($this->list[$offset]);
        }
    }
}
