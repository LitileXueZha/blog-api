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
                'type' => 'string',
                'required' => true,
                'error' => '文章名称不能为空',
            ],
            'category' => [
                'type' => 'enum',
                'required' => true,
                'enum' => ['note', 'life'],
                'error' => '文章类别需为笔记或生活',
            ],
        ];

        $msg = Util::validate($data ?: [], $rules);

        // 规则校验失败
        if ($msg) {
            $res = new Response(HttpCode::BAD_REQUEST);

            $res->setErrorMsg($msg);
            $res->end();
            return;
        }

        $record = MMA::add($data);
        $res = new Response(HttpCode::OK, $record);

        $res->end();
    }

    /**
     * 获取一篇文章
     * 
     * @param Array 请求信息
     */
    public static function get($req)
    {
        $id = $req['params']['id'];
        // 筛选未逻辑删除字段
        $params = ['article_id' => $id, '_d' => 0];
        $rows = MMA::get($params);

        // 不存在此条记录，返回 404
        if (count($rows) === 0) {
            $res = new Response(HttpCode::NOT_FOUND);

            $res->setErrorMsg('请求的文章不存在');
            $res->end();
            return;
        }

        $res = new Response(HttpCode::OK, $rows[0]);

        $res->end();
    }

    /**
     * 更新一篇文章
     * 
     * @param Array 请求信息
     */
    public static function update($req)
    {
        $id = $req['params']['id'];
        $params = $req['data'];
        // 可供更新的字段
        $updatableKeys = ['title', 'summary', 'content', 'tag', 'status', 'category', 'bg'];

        // 过滤不可更新字段
        $params = array_filter(/** 简写的三目运算符 */$params ?: [], function ($key) use ($updatableKeys) {
            return in_array($key, $updatableKeys);
        }, ARRAY_FILTER_USE_KEY);
        

        // 无数据，返回 400
        if (empty($params)) {
            $res = new Response(HttpCode::BAD_REQUEST);

            $res->setErrorMsg('没有要更新的数据');
            $res->end();
            return;
        }

        // 筛选未逻辑删除字段
        $params['_d'] = 0;

        MMA::set($id, $params);
    }
}
