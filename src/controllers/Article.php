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
        $rules = [
            'title' => [
                [
                    'type' => 'string',
                    'required' => true,
                    'error' => '文章名称不能为空',
                ],
            ],
            'category' => [
                'type' => 'enum',
                'enum' => ['note', 'life'],
                'error' => '文章类别需为笔记或生活',
            ],
        ];

        $msg = Util::validate($data, $rules);

        if ($msg) {
            $res = new Response(400);

            $res->setErrorMsg($msg);
            $res->end();
        }
        // Log::debug($data);

        MMA::add($data);
    }
}
