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
     * 预处理语句集
     * 
     * @var Array
     */
    protected static $sql;

    /**
     * 添加一条文章
     * 
     * @param Array 文章数据
     * @return this
     */
    public static function add($data)
    {
        $db = DB::init();
        $tb = self::NAME;

        // 定义预处理语句
        if (empty(self::$sql['add'])) {
            $statement = "INSERT INTO $tb (title, summary, content, tag, category, bg, article_id)
                        VALUES (:title, :summary, :content, :tag, :category, :bg, :article_id)";
            self::$sql['add'] = $db->prepare($statement);
        }

        $sql = self::$sql['add'];
        
        // 绑定参数
        foreach ($data as $key => $value) {
            $sql->bindParam(":$key", $value);
        }

        // 插入数据
        $sql->execute();
    }

}
