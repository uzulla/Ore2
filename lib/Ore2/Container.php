<?php
declare(strict_types=1);
namespace Ore2;

use Psr\Container\ContainerInterface;
use Psr\Container\Exception\ContainerExceptionInterface;
use Psr\Container\Exception\NotFoundExceptionInterface;

/**
 * 素朴なオブジェクトコンテナ。setされたオブジェクトがClosureなら読み込み時に実行する
 * arrayと、__get/setアクセスに対応
 *
 * Class Container
 * @package Ore2
 */
class Container implements \ArrayAccess, ContainerInterface
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

    /*
     * PSR-11(Draft) implements
     * https://github.com/php-fig/fig-standards/blob/master/proposed/container.md
     */

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for this identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException('Not Found');
        }

        return $this->__get($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id)
    {
        return $this->offsetExists($id);
    }
}

class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
}

class ContainerException extends \Exception implements ContainerExceptionInterface
{
}