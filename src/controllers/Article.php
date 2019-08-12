<?php

/**
 * 文章的一系列逻辑，CURD
 */

require_once __DIR__.'/../models/Article.php';

use TC\Model\Article as MMA;

class Article
{
    /**
     * 获取一系列的文章
     * 
     * @param Array 请求信息
     */
    public static function list($req)
    {
        $params = $req['data'];
        $page = isset($params['page']) ? (int) $params['page'] : 1;
        $size = isset($params['size']) ? (int) $params['size'] : 10;
        // 不合法的页数设为默认 1
        if ($page <= 0) $page = 1;
        if ($size < 0 ) $size = 10;

        $limit = $size * ($page - 1) . ", $size";

        // 可供查询的字段
        $selectableKeys = ['tag', 'status', 'category'];
        $params = Util::filter($params ?: [], $selectableKeys);        
        
        // 筛选未删除字段
        $params['_d'] = 0;
        $rows = MMA::get($params, ['limit' => $limit]);
        $res = new Response(HttpCode::OK, $rows);

        $res->end();
    }

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
                'error' => '文章名称不正确',
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
            self::bad($msg);
            return;
        }

        // 可供添加的字段
        $keys = ['title', 'summary', 'content', 'tag', 'category', 'bg'];
        $data = Util::filter($data, $keys);

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
        $res = MMA::get($params);
        $rows = $res['items'];

        // 不存在此条记录，返回 404
        if (count($rows) === 0) {
            self::notFound();
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
        $data = $req['data'];
        $rules = [
            'title' => [
                'type' => 'string',
                'error' => '文章名称不正确',
            ],
            'summary' => [
                'type' => 'string',
                'error' => '文章简介不正确',
            ],
            'content' => [
                'type' => 'string',
                'error' => '文章内容不正确',
            ],
            'status' => [
                'type' => 'number',
                'error' => '文章状态需为数字',
                'validator' => function ($data) {
                    if (!in_array($data, [0, 1, 2, 3])) {
                        return '文章状态不正确';
                    }
                },
            ],
            'category' => [
                'type' => 'enum',
                'enum' => ['note', 'life'],
                'error' => '文章类别需为笔记或生活',
            ],
            'bg' => [
                'type' => 'string',
                'error' => '文章背景图不正确',
            ],
        ];

        $msg = Util::validate($data, $rules);

        // 规则校验失败
        if ($msg) {
            self::bad($msg);
            return;
        }

        // 可供更新的字段
        $updatableKeys = ['title', 'summary', 'content', 'tag', 'status', 'category', 'bg'];
        // 过滤不可更新字段
        $data = Util::filter(/** 简写的三目运算符 */$data ?: [], $updatableKeys);        

        // 无数据，返回 400
        if (empty($data)) {
            self::bad('没有要更新的数据');
            return;
        }

        // 筛选未逻辑删除字段
        $data['_d'] = 0;

        $rows = MMA::set($id, $data);

        // 不存在此条记录，返回 404
        if (count($rows) === 0) {
            self::notFound();
            return;
        }

        $res = new Response(HttpCode::OK, $rows[0]);

        $res->end();
    }

    /**
     * 文章不存在
     * 
     * 统一返回 404 逻辑，复用代码
     */
    private static function notFound()
    {
        $res = new Response(HttpCode::NOT_FOUND);

        $res->setErrorMsg('请求的文章不存在');
        $res->end();
    }

    /**
     * 文章数据有误
     * 
     * 统一返回 400 逻辑
     * 
     * @param String 错误信息
     */
    private static function bad($msg)
    {
        $res = new Response(HttpCode::BAD_REQUEST);

        $res->setErrorMsg($msg);
        $res->end();
    }
}
