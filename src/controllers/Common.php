<?php

/**
 * 一些公共请求逻辑
 */

class Common extends BaseController
{
    /**
     * 生成 token 接口
     */
    public static function oauth($req)
    {
        Log::debug($_SERVER);
        $headers = $req['headers'];
        $uid = '';

        if (empty($headers['AUTHORIZATION'])) {
            // TODO: 数据库生成用户
            $uid = 'test';
            $userAgent = $headers['USER_AGENT'];
            $host = $headers['HOST'];
            $ip = $_SERVER['REMOTE_ADDR'];
        } else if ($token = Auth::parse($headers['AUTHORIZATION'])) {
            $uid = $token['uid'];
        }

        $tokenStr = Auth::generate($uid);
        $res = new Response(HttpCode::OK, $tokenStr);

        $res->end();
    }

    /**
     * 管理用户登录接口
     */
    public static function userLogin($req)
    {
        echo 1;
    }
}