<?php

/**
 * 文章 - 数据模型
 */

require_once('./DB.php');

namespace \TC\Model;

class Article extends DB
{
    /**
     * @var String 数据库表名称
     */
    const NAME = 'article';
    // 本次连接
    private $dbh = null;

    function __construct()
    {
        // 连接数据库
        $dbh = super.connect();

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
