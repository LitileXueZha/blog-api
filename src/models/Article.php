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
     * 本次数据库连接
     * 
     * @var \PDO
     */
    private $dbh = null;

    function __construct()
    {
        // 连接数据库
        $dbh = DB::init();

        $this->dbh = $dbh;
    }

    /**
     * 添加一条文章
     * 
     * @param Array 文章数据
     * @return this
     */
    public function add($data)
    {
        $sql = $this->ppAdd;
        $tb = static::NAME;

        if (empty($sql)) {
            // 定义预处理语句
            $sql = $this->dbh->prepare("INSERT INTO $tb (title, summary, content, tag, )");
        }

        return $this;
    }

}
