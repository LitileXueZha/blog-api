<?php

/**
 * 文章 - 数据模型
 */

namespace TC\Model;

require_once __DIR__.'/DB.php';

class Article
{
    /**
     * 数据库表名称
     * 
     * @var String
     */
    const NAME = 'article';

    /**
     * 查询返回格式
     * 
     * @var String
     */
    const FORMAT = 'article_id as id, title, summary, content, tag,
                    status, category, bg, create_at';

    /**
     * 添加一条文章
     * 
     * @param Array 文章数据
     * @return Array 插入成功后的记录
     */
    public static function add($data)
    {
        $db = DB::init();
        $tb = self::NAME;

        // 唯一 id 生成
        $data['article_id'] = DB::shortId();

        $columns = array_keys($data);
        [$col, $placeholder] = DB::getPlaceholderByKeys($columns);
        
        $statement = "INSERT INTO $tb ($col) VALUES ($placeholder)";

        $sql = $db->prepare($statement);

        // 绑定参数
        foreach ($data as $key => $value) {
            $sql->bindValue(":$key", $value);
        }

        // 插入数据
        $sql->execute();

        $res = static::get(['article_id' => $data['article_id']]);

        return $res[0];
    }

    /**
     * 获取文章
     * 
     * @param Array 查询条件
     * @return Array 文章记录
     */
    public static function get($params)
    {
        $db = DB::init();
        $tb = self::NAME;
        $format = self::FORMAT;

        $columns = array_keys($params);
        $placeholder  = implode(' AND ', array_map(function ($key) {
            return "$key = :$key";
        }, $columns));

        $statement = "SELECT $format FROM $tb WHERE $placeholder";

        $sql = $db->prepare($statement);

        foreach ($columns as $key) {
            $sql->bindValue(":$key", $params[$key]);
        }

        $sql->execute();

        $res = $sql->fetchAll();

        return $res;
    }

    /**
     * 更新文章
     * 
     * @param String 文章 id
     * @param Array 需要更新的数据
     * @return Array 更新后的记录
     */
    public static function set($id, $data)
    {
        $db = DB::init();
        $tb = self::NAME;

        $columns = array_keys($data);
        $placeholder = implode(',', array_map(function ($key) {
            return "$key=:$key";
        }, $columns));

        $statement = "UPDATE $tb SET $placeholder WHERE article_id=:id";

        $sql = $db->prepare($statement);

        foreach ($columns as $key) {
            $sql->bindValue(":$key", $data[$key]);
        }
        // 防止 id 被 sql 注入
        $sql->bindValue(':id', $id);

        $sql->execute();

        // 更新完后去查最新的数据，如果为空，那么此条数据不存在
        $res = static::get(['article_id' => $id]);

        return $res;
    }
}
