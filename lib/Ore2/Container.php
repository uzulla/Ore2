<?php
declare(strict_types=1);
namespace Ore2;

/**
 * 素朴なオブジェクトコンテナ。setされたオブジェクトがClosureなら読み込み時に実行する
 * arrayと、__get/setアクセスに対応
 *
 * Class Container
 * @package Ore2
 */
class Container implements \ArrayAccess
{
    /** @var array シングルトンで取得できるコンテナを保存しておく配列 */
    static $instanceList = [];

    /**
     * 素朴なシングルトン化、引数でマルチトン風
     * @param string $key
     */
    public function keepInstance($key = "_")
    {
        static::$instanceList[$key] = $this;
    }

    /**
     * 素朴なシングルトン取得
     * @param string $key
     */
    static function pickInstance($key = "_")
    {
        return static::$instanceList[$key];
    }

    /** @var array オブジェクト保存配列 */
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
