<?php

/**
 * 数据库连接
 */

namespace TC\Model;

use PDO;
use Util;

class DB
{
    /**
     * 连接实例
     * 
     * @var PDO
     */
    private static $db;

    // 数据库连接状态
    public static $connecting = false;

    /**
     * 获取连接实例
     * 
     * @return PDO
     */
    public static function init()
    {
        if (!self::$connecting) {
            self::connect();
        }

        return self::$db;
    }
    
    /**
     * 数据库连接
     * 
     * @return PDO 数据库实例
     */
    public static function connect()
    {
        $host = DB_HOST;
        $port = DB_PORT;
        $dbname = DB_NAME;
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
        $db = new PDO($dsn, DB_USER, DB_PASSWORD);

        self::$connecting = true;
        self::$db = &$db;
    }

    /**
     * 销毁数据库连接实例
     * 
     * 一般来说，PHP 脚本运行完成后会自动销毁
     */
    public static function destroy()
    {
        self::$db = null;
        self::$connecting = false;
    }

    /**
     * 生成短链型 id
     * 
     * 所有数据，统一使用此 count 表里的
     * 
     * @return String 短链型 id
     */
    public static function shortId()
    {
        $db = self::init();
        $tb = 'count';
        $sql = "INSERT INTO $tb VALUES ()";

        $query = $db->query($sql);
        $num = $db->lastInsertId();

        $id = Util::shortId($num);

        return $id;
    }
}
