<?php

/**
 * 评论的一系列逻辑，CURD
 */

require_once __DIR__.'/../models/Comment.php';

use TC\Model\Comment as MMC;

class Comment extends BaseController
{
    /**
     * 获取评论列表
     * 
     * @param Array 请求信息
     */
    public static function list($req)
    {
        $params = $req['data'];
        $limit = self::getLimitByQuery($params);

        // 可供查询的字段
        $keys = ['parent_id'];
        // 管理后台可查询文章、留言等分类下的评论
        $keysAdmin = ['parent_id', 'type'];
        $params = Util::filter($params, $keys);

        // 必填字段。未传直接返回空
        // 为什么不返回 400？这是个 GET 请求，只是返回空数据对 API 兼容性更好
        if (!isset($params['parent_id'])) {
            $res = new Response(HttpCode::OK, ['total' => 0, 'items' => []]);

            $res->end();
            return;
        }

        // 筛选未删除数据
        $params['_d'] = 0;
        $rows = MMC::get($params, ['limit' => $limit]);
        $res = new Response(HttpCode::OK, $rows);

        $res->end();
    }
    
    /**
     * 创建评论
     * 
     * @param Array 请求信息
     */
    public static function create($req)
    {
        $data = $req['data'];
        $rules = [
            'parent_id' => [
                'type' => 'string',
                'required' => true,
                'error' => '评论主体不能为空',
            ],
            // 'name' => [
            //     'type' => 'string',
            //     'required' => true,
            //     'error' => '评论人姓名不能为空',
            // ],
            'content' => [
                'type' => 'string',
                'required' => true,
                'error' => '评论内容不能为空',
            ],
            'type' => [
                'type' => 'enum',
                'required' => true,
                // 目前只有 0-文章、1-留言类型
                'enum' => [0, 1],
                'error' => '评论主体类型不正确',
            ],
        ];

        $msg = Util::validate($data, $rules);

        if ($msg) {
            self::bad($msg);
            return;
        }

        // 可添加的字段
        $keys = ['parent_id', 'name', 'content', 'type', 'label'];
        $data = Util::filter($data, $keys);

        $record = MMC::add($data);
        $res = new Response(HttpCode::OK, $record);

        $res->end();
    }

    /**
     * 获取单条评论
     * 
     * @param Array 请求信息
     */
    public static function read($req)
    {
        $id = $req['params']['id'];
        $params = ['comment_id' => $id, '_d' => 0];

        $rows = MMC::get($params)['items'];

        // 无数据
        if (count($rows) === 0) {
            self::notFound();
            return;
        }

        $res = new Response(HttpCode::OK, $rows[0]);

        $res->end();
    }

    /**
     * 更新单条评论
     * 
     * TODO: 仅管理后台可操作
     * @param Array 请求信息
     */
    public static function update($req)
    {
        $id = $req['params']['id'];
        $data = $req['data'];
        $rules = [
            'name' => [
                'type' => 'string',
                'error' => '评论人姓名不能为空',
            ],
            'content' => [
                'type' => 'string',
                'error' => '评论内容不能为空',
            ],
            'type' => [
                'type' => 'enum',
                // 目前只有 0-文章、1-留言类型
                'enum' => [0, 1],
                'error' => '评论主体类型不正确',
            ],
        ];

        $msg = Util::validate($data, $rules);

        // 规则校验失败
        if ($msg) {
            self::bad($msg);
            return;
        }

        // 可供更新的字段
        $keys = ['name', 'content', 'type'];
        $data = Util::filter($data, $keys);

        // 无数据，返回 400
        if (empty($data)) {
            self::bad('没有可更新的数据');
            return;
        }

        $rows = MMC::set($id, $data);

        // 不存在此条记录，返回 404
        if (count($rows) === 0) {
            self::notFound();
            return;
        }

        $res = new Response(HttpCode::OK, $rows[0]);

        $res->end();
    }

    /**
     * 删除一条评论
     * 
     * TODO: 仅管理后台可操作
     * @param Array 请求信息
     */
    public static function delete($req)
    {
        $id = $req['params']['id'];
        $count = MMC::delete($id);

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
     * 管理后台获取全部评论列表
     * 
     * @param Array 请求信息
     */
    public static function all($req)
    {
        $params = $req['data'];
        $limit = self::getLimitByQuery($params);

        // 可供查询的字段
        $keys = ['parent_id', 'type', 'label'];
        $params = Util::filter($params, $keys);

        // 筛选未删除数据
        $params['_d'] = 0;
        $rows = MMC::get($params, ['limit' => $limit]);
        $res = new Response(HttpCode::OK, $rows);

        $res->end();
    }
}
