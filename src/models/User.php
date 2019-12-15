<?php

/**
 * 用户 - 数据模型
 */

namespace TC\Model;

require_once __DIR__.'/DB.php';

use DBStatement;

class User
{
    /**
     * 用户表名
     * 
     * @var String
     */
    const NAME = 'user';

    /**
     * 创建用户
     */
    public static function add($data)
    {
        $db = DB::init();
        $tb = self::NAME;

        $statment = "INSERT INTO";
    }
}