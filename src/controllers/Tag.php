<?php

/**
 * 标签的一系列逻辑，CURD
 */

require_once __DIR__.'/../models/Tag.php';

use TC\Model\Tag as MMT;

class Tag extends BaseController
{
    /**
     * 获取一系列标签
     * 
     * @param Array 请求信息
     */
    public static function list($req)
    {
        $params = $req['data'];
        // 可供查询的字段
        $keys = ['status'];
        $params = Util::filter($params, $keys);

        // 筛选未删除
        $params['_d'] = 0;
        $rows = MMT::get($params);
        $res = new Response(HttpCode::OK, $rows);

        $res->end();
    }

    /**
     * 获取单个标签
     * 
     * @param Array 请求信息
     */
    public static function read($req)
    {
        $id = $req['params']['id'];
        $params = [ 'name' => $id, '_d' => 0 ];
        // 使用模型查数据
        $rows = MMT::get($params)['items'];

        if (count($rows) === 0) {
            self::notFound();
            return;
        }

        $res = new Response(HttpCode::OK, $rows[0]);

        $res->end();
    }

    /**
     * 创建标签
     * 
     * @param Array 请求信息
     */
    public static function create($req)
    {
        $data = $req['data'];
        $rules = [
            'id' => [
                'type' => 'string',
                'required' => true,
                'error' => '标签id不能为空',
            ],
            'name' => [
                'type' => 'string',
                'required' => true,
                'error' => '标签名不能为空'
            ],
            'status' => [
                'type' => 'enum',
                'enum' => [1, 2],
                'error' => '标签状态不正确',
            ],
        ];

        $msg = Util::validate($data, $rules);
        // 校验有误
        if ($msg) {
            self::bad($msg);
            return;
        }

        // 转化为数据库里的字段
        $data = [
            'name' => $data['id'],
            'display_name' => $data['name'],
            'status' => $data['status'],
        ];

        $rows = MMT::get(['name' => $data['name'], '_d' => 0]);
        // 防重校验
        if (count($rows['items']) > 0) {
            self::bad('标签id已存在');
            return;
        }

        $row = MMT::add($data);
        $res = new Response(HttpCode::OK, $row);

        $res->end();
    }

    /**
     * 更新标签
     * 
     * @param Array 请求信息
     */
    public static function update($req)
    {
        $id = $req['params']['id'];
        $data = $req['data'];
        $rules = [
            'name' => [
                'type' => 'string',
                'error' => '标签名不正确',
            ],
            'status' => [
                'type' => 'enum',
                'enum' => [1, 2],
                'error' => '标签状态不正确',
            ],
        ];

        $msg = Util::validate($data, $rules);

        if ($msg) {
            self::bad($msg);
            return;
        }

        $keys = ['name', 'status'];
        $updateData = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $keys)) {
                // 转化字段
                if ($key === 'name') $key = 'display_name';

                $updateData[$key] = $value;
            }
        }

        if (count($updateData) === 0) {
            self::bad('没有可更新的数据');
            return;
        }

        $rows = MMT::set($id, $updateData);

        if (count($rows) === 0) {
            self::notFound();
            return;
        }

        $res = new Response(HttpCode::OK, $rows[0]);

        $res->end();
    }

    /**
     * 删除标签
     * 
     * @param Array 请求信息
     */
    public static function delete($req)
    {
        $id = $req['params']['id'];
        $count = MMT::delete($id);

        if ($count === 0) {
            self::notFound();
            return;
        }

        // 返回数据为 null，代表数据被删除了
        $res = new Response(HttpCode::OK, NULL);

        $res->end();
    }

    public static function click($req)
    {
        $res = new Response(HttpCode::NO_CONTENT);

        if (isset($req['data']['id'])) {
            // 有效标签 id 统计
            MMT::incrClick($req['data']['id']);
        }

        $res->end();
    }
}
