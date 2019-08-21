<?php

/**
 * 控制器基类
 * 
 * 定义了一些控制器共用的方法，例如返回 404 逻辑
 */

class BaseController
{
    /**
     * 获取数据库分页字段
     * 
     * 根据 query 参数获取分页字段 page、size。默认为 0、10
     * 另外将不合法的数据转化为默认，例如 -10
     * 
     * @param Array 请求查询参数
     * @return String 数据库 LIMIT 字符串。例如 "0, 10"
     */
    protected static function getLimitByQuery($query)
    {
        $page = isset($query['page']) ? (int) $query['page'] : 1;
        $size = isset($query['size']) ? (int) $query['size'] : 10;
        // 不合法的页数重设为默认 1
        if ($page <= 0) $page = 1;
        if ($size < 0 ) $size = 10;

        return $size * ($page - 1) . ", $size";
    }

    /**
     * 数据不存在
     * 
     * 统一返回 404 逻辑，复用代码
     */
    protected static function notFound()
    {
        $res = new Response(HttpCode::NOT_FOUND);

        $res->setErrorMsg('请求的数据不存在');
        $res->end();
    }

    /**
     * 数据有误
     * 
     * 统一返回 400 逻辑
     * 
     * @param String 错误信息
     */
    protected static function bad($msg)
    {
        $res = new Response(HttpCode::BAD_REQUEST);

        $res->setErrorMsg($msg);
        $res->end();
    }
}
