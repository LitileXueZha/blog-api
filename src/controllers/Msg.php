<?php

/**
 * 留言的一系列逻辑，CURD
 */

require_once __DIR__.'/../models/Msg.php';

use TC\Model\Msg as MM;

class Msg extends BaseController
{
    /**
     * 获取一系列留言
     * 
     * @param Array 请求信息
     */
    public static function list($req)
    {
        $params = $req['data'];
        $limit = self::getLimitByQuery($params);

        // 可供查询的字段
        // 目前留言不支持字段筛选，暂无此需求
        $keys = [];
        $params = Util::filter($params, $keys);

        // 筛选未删除
        $params['_d'] = 0;

        $rows = MM::get($params, ['limit' => $limit]);
        $res = new Response(HttpCode::OK, $rows);

        $res->end();
    }

    /**
     * 获取单条留言
     * 
     * @param Array 请求信息
     */
    public static function read($req)
    {
        $id = $req['params']['id'];
        $params = [ 'msg_id' => $id, '_d' => 0 ];

        $rows = MM::get($params)['items'];

        // 无数据
        if (count($rows) === 0) {
            self::notFound();
            return;
        }

        $res = new Response(HttpCode::OK, $rows[0]);

        $res->end();
    }

    /**
     * 创建一条留言
     * 
     * @param Array 请求信息
     */
    public static function create($req)
    {
        $data = $req['data'];
        $rules = [
            'name' => [
                'type' => 'string',
                'required' => true,
                'error' => '留言人姓名不能为空',
            ],
            // NOTE: 如果之后要校验长度，使用 mbstring 提供的方法
            'content' => [
                'type' => 'string',
                'required' => true,
                'error' => '留言内容不能为空',
            ],
            'platform' => [
                'type' => 'enum',
                'required' => true,
                'enum' => ['pc', 'mobile'],
                'error' => '留言平台需为pc或mobile',
            ],
        ];

        $msg = Util::validate($data, $rules);

        // 规则校验失败
        if ($msg) {
            self::bad($msg);
            return;
        }

        // 可供添加的字段
        $keys = ['name', 'content', 'avatar', 'platform'];
        $data = Util::filter($data, $keys);

        $record = MM::add($data);
        $res = new Response(HttpCode::OK, $record);

        $res->end();
    }

    /**
     * 更新一条留言
     * 
     * 1. 用户端只能更新头像
     * 2. 管理后台可以对内容进行过滤，更新内容。不建议后台更改用户其它数据
     * 
     * @param Array 请求信息
     */
    public static function update($req)
    {
        $id = $req['params']['id'];
        $data = $req['data'];
        $rules = [
            'avatar' => [
                // 此类型表示不确定，例如 null 代表删除，也可更新
                'type' => 'mixed',
                'validator' => function ($data) {
                    // 如果为对象，会导致 sql 预处理语句绑定值时失败
                    // FIXME: 还有其它地方，会导致攻击漏洞！
                    if (is_array($data)) {
                        return '头像不正确';
                    }
                },
            ],
        ];

        $msg = Util::validate($data, $rules);

        // 规则校验失败
        if ($msg) {
            self::bad($msg);
            return;
        }

        // 可更新的字段
        $keys = ['avatar'];
        // 管理后台可更新更多
        $keysAdmin = ['avatar', 'name', 'content'];
        $data = Util::filter($data, $keys);

        // 无更新数据，返回 400
        if (count($data) === 0) {
            self::bad('没有可更新的数据');
            return;
        }

        $rows = MM::set($id, $data);

        // 无此留言
        if (count($rows) === 0) {
            self::notFound();
            return;
        }

        $res = new Response(HttpCode::OK, $rows[0]);

        $res->end();
    }

    /**
     * 删除留言
     * 
     * @param Array 请求信息
     */
    public static function delete($req)
    {
        $id = $req['params']['id'];
        $count = MM::delete($id);

        if ($count === 0) {
            self::notFound();
            return;
        }

        // 返回数据为 null，代表数据被删除了
        $res = new Response(HttpCode::OK, NULL);

        $res->end();
    }
}

