<?php
namespace MyApp\Db;

use MyApp\Db;

/**
 * 時間がなかったんや…
 * Class Post
 * @package MyApp\Db
 */
class Post extends Db
{
    public function getAll()
    {
        $stm = static::$pdo->prepare("SELECT * FROM post ORDER BY id");
        $stm->execute();
        return $stm->fetchAll();
    }

    public function get($id)
    {
        $stm = static::$pdo->prepare("SELECT * FROM post WHERE id=:id");
        $stm->execute(array('id' => $id));
        return $stm->fetch();
    }

    public function insert($name, $text)
    {
        $stm = static::$pdo->prepare("INSERT INTO post ('name', 'text', 'time') VALUES (:name, :text, :time)");
        $stm->execute(array(
            'name' => $name,
            'text' => $text,
            'time' => time(),
        ));
    }

    public function reset()
    {
        static::$pdo->query('DROP TABLE post;');
        static::$pdo->query(
            '
        CREATE TABLE post
        (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name text NOT NULL,
        text text NOT NULL,
        time text NOT NULL
        )
        ');
    }

}

