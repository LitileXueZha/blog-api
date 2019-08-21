<?php

/**
 * 留言 - 标签模型
 */

namespace TC\Model;

require_once __DIR__.'/DB.php';

class Msg
{
    /**
     * 数据库表名
     * 
     * @var String
     */
    const NAME = 'msg';

    /**
     * 查询返回格式
     * 
     * @var String
     */
    const FORMAT = "msg_id as id, name, content, avatar, platform,
                    create_at";

    /**
     * 创建留言
     * 
     * @param Array 留言数据
     * @param Array 插入成功后的记录
     */
    public static function add($data)
    {
        $db = DB::init();
        $tb = self::NAME;

        // 唯一 id 生成
        $data['msg_id'] = DB::shortId();

        // 获取 sql 语句占位符
        $columns = array_keys($data);
        [$col, $placeholder] = DB::getPlaceholderByKeys($columns);

        $statement = "INSERT INTO $tb ($col) VALUES ($placeholder)";

        $sql = $db->prepare($statement);

        // 绑定数据。烦
        foreach ($data as $key => $value) {
            $sql->bindValue(":$key", $value);
        }

        // 插入
        $sql->execute();

        $res = self::get(['msg_id' => $data['msg_id']]);

        return $res['items'][0];
    }

    /**
     * 获取留言
     * 
     * @param Array 查询条件
     * @param Array 额外参数。例如 LIMIT、ORDER BY
     * @return Array 留言数据。格式为 [ 'total', 'items' ]
     */
    public static function get($params, $options = [])
    {
        $db = DB::init();
        $tb = self::NAME;
        // 查询格式
        $format = self::FORMAT;
        // 分页
        $limit = empty($options['limit']) ? '0, 10' : $options['limit'];

        $columns = array_keys($params);
        $placeholder = implode(' AND ', array_map(function ($key) {
            return "$key = :$key";
        }, $columns));

        $statement = "SELECT SQL_CALC_FOUND_ROWS $format FROM $tb WHERE $placeholder LIMIT $limit";

        $sql = $db->prepare($statement);

        foreach ($params as $key => $value) {
            $sql->bindValue(":$key", $value);
        }

        $sql->execute();

        $res = $sql->fetchAll();
        $sqlCount = $db->query("SELECT FOUND_ROWS()");
        $count = $sqlCount->fetch();

        return [
            'total' => $count['FOUND_ROWS()'],
            'items' => $res,
        ];
    }
}
