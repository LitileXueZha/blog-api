<?php

/**
 * 文章的一系列逻辑，CURD
 */

require_once __DIR__.'/../models/Article.php';

use TC\Model\Article as MMA;

class Article extends BaseController
{
    /**
     * 获取一系列的文章
     * 
     * @param Array 请求信息
     */
    public static function list($req)
    {
        $params = $req['data'];
        $limit = self::getLimitByQuery($params);

        // 可供查询的字段
        $selectableKeys = ['tag', 'status', 'category'];
        $params = Util::filter($params, $selectableKeys);        
        
        // 筛选未删除字段
        $params['_d'] = 0;
        $rows = MMA::get($params, ['limit' => $limit]);

        // 去除 `content` 字段，列表不返回文章具体内容
        foreach ($rows['items'] as &$item) {
            unset($item['content']);
        }

        $res = new Response(HttpCode::OK, $rows);

        $res->end();
    }

    /**
     * 添加一篇文章
     * 
     * @param Array 请求信息
     */
    public static function create($req)
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
            'status' => [
                'type' => 'enum',
                'enum' => [1],
                'error' => '文章状态需为上线',
            ],
        ];

        $msg = Util::validate($data, $rules);

        // 规则校验失败
        if ($msg) {
            self::bad($msg);
            return;
        }

        // 可供添加的字段
        $keys = ['title', 'summary', 'content', 'text_content', 'status', 'tag', 'category', 'bg'];
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
    public static function read($req)
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
            'status' => [
                'type' => 'enum',
                'enum' => [0, 1, 2, 3],
                'error' => '文章状态不正确',
            ],
            'category' => [
                'type' => 'enum',
                'enum' => ['note', 'life'],
                'error' => '文章类别需为笔记或生活',
            ],
        ];

        $msg = Util::validate($data, $rules);

        // 规则校验失败
        if ($msg) {
            self::bad($msg);
            return;
        }

        // 可供更新的字段
        $updatableKeys = ['title', 'summary', 'content', 'text_content', 'tag', 'status', 'category', 'bg'];
        // 过滤不可更新字段
        $data = Util::filter($data, $updatableKeys);        

        // 无数据，返回 400
        if (empty($data)) {
            self::bad('没有可更新的数据');
            return;
        }

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
     * 删除一篇文章
     * 
     * @param Array 请求信息
     */
    public static function delete($req)
    {
        $id = $req['params']['id'];
        $count = MMA::delete($id);

        // 已被删除或不存在，返回 404
        if ($count === 0) {
            self::notFound();
            return;
        }

        // 返回一个 NULL，代表着这个数据为空，被删掉了
        $res = new Response(HttpCode::OK, NULL);

        $res->end();
    }

    /**
     * 获取文章垃圾箱列表
     * 
     * @param Array 请求信息
     */
    public static function getTrashList($req)
    {
        $params = $req['data'];
        $limit = self::getLimitByQuery($params);
        
        // 不支持其它字段筛选
        $params = [
            'status' => 3,
            // 筛选未删除字段
            '_d' => 0,
        ];
        $rows = MMA::trash($params, [
            'limit' => $limit,
            'orderBy' => 'modify_at DESC', // 根据移动到垃圾箱时间倒序
        ]);

        foreach ($rows['items'] as &$item) {
            // 文章摘要为空时，返回部分文章内容
            if (empty($item['summary']) && !empty($item['text_content'])) {
                $item['summary'] = mb_substr($item['text_content'], 0, 130);
            }
            unset($item['text_content']);
        }

        $res = new Response(HttpCode::OK, $rows);

        $res->end();
    }
}
