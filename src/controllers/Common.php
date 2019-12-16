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
        // Log::debug($_SERVER);
        $headers = $req['headers'];
        $uid = '';

        if (empty($headers['AUTHORIZATION'])) {
            // TODO: 数据库生成用户
            $uid = '__ADMIN__';

            // 没有来源，默认为接口域名
            if (empty($headers['ORIGIN'])) {
                $headers['ORIGIN'] = $_SERVER['REQUEST_SCHEME'] .'://'. $headers['HOST'];
            }

            $ip = $_SERVER['REMOTE_ADDR'];
            $params = [
                // TODO: ip 地理位置查询
                'user_ip' => $ip,
                'user_ip_address' => IPSearch::ip138($ip),
                'user_origin' => $headers['ORIGIN'],
                'user_agent' => $headers['USER_AGENT'],
            ];
            Log::debug($params);
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