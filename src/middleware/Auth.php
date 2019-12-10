<?php

/**
 * 鉴权
 * 
 * + 在 header 中携带 Authorization 参数
 * + 签名
 * + 跳过 OPTIONS 请求
 */

class Auth implements Middleware
{
    public function execute($app, $next)
    {
        if ($app::$req['method'] === 'OPTIONS') {
            $res = new Response(HttpCode::NO_CONTENT);
            $res->end();
            // 取消跳转剩下的中间件，直接跳过
            // $next();
            return;
        }

        if (empty($app::$req['headers']['AUTHORIZATION'])) {
            // 未认证
            $res = new Response(HttpCode::UNAUTHORIZED);
            $res->setErrorMsg('未认证');
            $res->end();
            return;
        }
        
        
        $atk = $app::$req['headers']['AUTHORIZATION'];

        if ($atk !== 'Bearer tao') {
            // token 签名校验失败
            $res = new Response(HttpCode::UNAUTHORIZED);
            $res->setErrorMsg('认证失败');
            $res->end();
            return;
        }

        // 校验通过，进入下个中间件
        $next();
    }

    public function fallback($app)
    {
        // Nothing.
    }
}
