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
     * 
     * @param Array $data 用户数据
     * @return Array 插入成功后的记录
     */
    public static function add($data)
    {
        $db = DB::init();
        $tb = self::NAME;

        // 唯一 id 生成
        $data['user_id'] = DB::shortId();

        $columns = array_keys($data);
        $dbs = new DBStatement($db);

        // dbs 操作
        $dbs->insert($columns);

        $statement = $dbs->toString();
        $sql = $db->prepare($statement);

        // 数据绑定
        foreach ($data as $key => $value) {
            $sql->bindValue(":$key", $value);
        }

        // 执行 sql
        $sql->execute();
    }

    /**
     * 查询用户
     * 
     * @param Array 查询条件
     * @param Array 额外的参数。例如 LIMIT、ORDER BY
     * @return Array 用户数据。格式为 [ 'total', 'items' ]
     */
    public static function get($params, $options = [])
    {
        $db = DB::init();
        $tb = self::NAME;

        $columns = array_keys($params);
        $dbs = new DBStatement($tb);

        // dbs 查询
        $dbs->select(
                'account, display_name, avatar, pwd',
                'user_ip, user_ip_address, user_origin, user_agent',
                'user_id as id, create_at'
            )
            ->where($columns);
        
        $statement = $dbs->toString();
        $sql = $db->prepare($statement);
    }
}