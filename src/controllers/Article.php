<?php

/**
 * 文章的一系列逻辑，CURD
 */

require_once __DIR__.'/../models/Article.php';

use TC\Model\Article as ModelArticle;

class Article
{
    private static $mod;

    private static function getModel()
    {
        if (empty(static::$mod)) {
            static::$mod = new ModelArticle();
        }

        return static::$mod;
    }

    /**
     * 添加一篇文章
     * 
     * @param Array 请求信息
     */
    public static function add($req)
    {
        $db = new ModelArticle();
        $data = $req['data'];

        Log::debug(static::$db);
    }
}
