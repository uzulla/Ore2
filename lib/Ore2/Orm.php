<?php
namespace Ore2;

abstract class Pdo extends Row
{
    static $pdo = null;
    static $config = [];

    static function setConfig($config)
    {
        static::$config = $config;
    }

    public function __construct()
    {
    }

    static function getPdo()
    {
        $options = array_merge(
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                \PDO::ATTR_PERSISTENT => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET SESSION sql_mode=\'TRADITIONAL,NO_AUTO_VALUE_ON_ZERO,ONLY_FULL_GROUP_BY\'',


            ],
            static::$config['options']
        );
        return new \PDO(
            static::$config['dsn'],
            static::$config['username'],
            static::$config['password'] .
            $options
        );
    }

    public function setPdo()
    {
        if (is_null($this->pdo)) $this->pdo = static::getPdo();
        return $this->pdo;
    }

    public function sqlQuery($sql, $params, $pdo = null)
    {
        if (is_null($pdo)) $pdo = static::setPdo();

        $sth = $pdo->prepare($sql);
//        foreach ($params as $p_key => $p_val) {
//            if (is_int($p_val)) {
//                $sth->bindValue(":{$p_key}", (int)$p_val, \PDO::PARAM_INT);
//            } else {
//                $sth->bindValue(":{$p_key}", $p_val, \PDO::PARAM_STR);
//            }
//        }
        $sth->execute($params);

        return $sth->fetchAll();
    }

    public function getsBySql($sql, $params, $pdo = null)
    {
        $res = $this->sqlQuery($sql, $params, $pdo);
        $list = [];
        foreach ($res as $row) {
            $row_object = new static();
            foreach ($row as $key => $col) {
                $row_object->data[$key] = $col;
            }
            $list[] = $row_object;
        }
        return $list;

    }

    public $pkey_name = 'id';
    public $table_name = 'test';

    public function getsById($id, $pdo = null)
    {
        return $this->getsBySql(
            "SELECT * FROM {$this->table_name} WHERE {$this->pkey_name}=:id",
            ['id' => $id],
            $pdo
        );
    }

    public function saveRow($pdo = null, $forceInsert = false)
    {
        $column_list = implode('`,`', array_keys($this->data));


        if ($forceInsert || !is_set($this->data[$this->pkey_name])) { // INSERT
            $this->sqlQuery(
                "INSERT INTO {$this->table_name} ({$column_list})"

            )

        }


    }

    public function deleteRow($id, $pdo = null)
    {
    }

}

class Row implements \ArrayAccess
{
    public $data = [];

    public function __set($key, $something)
    {
        $this->data[$key] = $something;
    }

    public function __get($key)
    {
        if (!isset($this->data[$key])) throw new \InvalidArgumentException('Undefined property');
        return $this->data[$key];
    }

    /*
     * implement ArrayAccess methods.
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
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
        if (isset($this->data[$offset])) {
            unset($this->data[$offset]);
        }
    }
}