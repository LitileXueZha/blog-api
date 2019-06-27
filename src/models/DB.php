<?php

/**
 * 数据库连接
 */

namespace TC\Model;

class DB
{
    function __construct()
    {
        
    }
    
    /**
     * 数据库连接
     * 
     * @return PDO 数据库实例
     */
    public static function connect()
    {
        $dsn = "mysql:host={DB_HOST};port={DB_PORT};dbname={DB_NAME}";
        $db = new PDO($dsn, DB_USER, DB_PASSWORD);

        return $db;
    }
}
