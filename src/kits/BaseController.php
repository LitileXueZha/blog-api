<?php

/**
 * 控制器基类
 * 
 * 定义了一些控制器共用的方法，例如返回 404 逻辑
 */

class BaseController
{
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
