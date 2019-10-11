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

        // 设置错误捕获
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // 设置默认 fetch mode，只有列名，没有数字索引
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // 数字转字符串的怪异现象
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

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

    /**
     * 占位字符转化
     * 
     * 根据 key 数组转化成 prepare 语句里的占位字符串
     * 
     * @example ['name', 'age'] 转化为 ['name,age', ':name,:age']
     * 
     * @param Array $keys key 数组
     * @return Array
     */
    public static function getPlaceholderByKeys($keys)
    {
        $column = implode(',', $keys);
        $placeholder = implode(',', array_map(function ($col) {
            return ":$col";
        }, $keys));

        return [$column, $placeholder];
    }

    /**
     * 查询条件转化
     * 
     * 根据定义的几个字段转化成
     * 
     * @see https://dev.mysql.com/doc/refman/8.0/en/select.html
     */
    public static function getQueryStr($options)
    {
        // TODO: xxx
    }

    /**
     * 读取配置，如果没有返回默认
     * 
     * @param Array $options 配置项
     * @return Array 配置项
     */
    public static function getOptsOrDefault($options)
    {
        // 目前支持的配置项
        $arr = [
            'limit' => '0, 10', // 默认分页 10 条
            'orderBy' => 'create_at DESC', // 默认创建时间倒序
        ];

        return array_merge($arr, $options);
    }
}
