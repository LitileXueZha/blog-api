<?php

/**
 * 鉴权
 * 
 * + 在 header 中携带 atk 参数
 * + 签名
 * + 跳过 OPTIONS 请求
 */

namespace Middleware;

use Middleware;

class Auth implements Middleware
{
    public function execute($app, $next)
    {
        // $atk = $app::$req['headers']['ATK'];
        if (empty($app::$req['headers']['ATK'])) {
            // 未认证
            $res = new Response(HttpCode::UNAUTHORIZED);
            $res->setErrorMsg('未认证');
            $res->end();
        }
        
        
        $atk = $app::$req['headers']['ATK'];

        if ($atk !== 'tao') {
            // token 签名校验失败
            $res = new Response(HttpCode::UNAUTHORIZED);
            $res->setErrorMsg('认证失败');
            $res->end();
        }
        $next();
    }

    public function fallback($app)
    {
        // Nothing.
    }
}
