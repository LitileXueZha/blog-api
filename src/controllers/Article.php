<?php

/**
 * 文章的一系列逻辑，CURD
 */

require_once __DIR__.'/../models/Article.php';

use TC\Model\Article as MMA;

class Article
{

    /**
     * 添加一篇文章
     * 
     * @param Array 请求信息
     */
    public static function add($req)
    {
        $data = $req['data'];
        Log::debug($data);

        MMA::add($data);
    }
}
