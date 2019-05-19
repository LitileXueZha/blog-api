<?php

require_once('./vendor/autoload.php');
require_once('./config.php');

require_once('./src/app.php');

class AuthMiddleware implements Middleware
{
    public function execute($app, $next)
    {
        $atk = $app::$req['headers']['ATK'];
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

App::use(new AuthMiddleware);

App::start();
