<?php

/**
 * 访问控制中间件
 */

require_once __DIR__.'/../models/AccessControl.php';

use \TC\Model\AccessControl as ACL;

class AccessControl implements Middleware
{
    public function execute($app, $next)
    {
        $req = $app::$req;
        $method = $req['method'];
        $url = $req['url'];
        $api = "$method $url";

        if (empty($req['AUTH_MIDDLEWARE']['user'])) {
            // 本次访问无用户，跳过
            $next();
            return;
        }

        $uid = $req['AUTH_MIDDLEWARE']['user'];
        $res = ACL::getacl($api, $uid);

        if (is_int($res) && $res === 0) {
            // 权限校验失败
            $res = new Response(HttpCode::FORBIDDEN);

            $res->setErrorMsg('无权限');
            return;
        }
        
        /**
         * 两种情况跳过：
         * 1. 校验成功
         * 2. 返回 `NULL`，而不是权限类型 `int`。说明权限列表中无此 api
         */
        $next();
    }

    public function fallback($app)
    {
        // Nothing
    }
}