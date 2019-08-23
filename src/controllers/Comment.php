<?php

/**
 * 评论的一系列逻辑，CURD
 */

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

        Log::debug($params);
    }
}
