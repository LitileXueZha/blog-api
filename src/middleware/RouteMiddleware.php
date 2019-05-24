<?php

/**
 * 路由中间件
 * 
 * kits/Route 只负责路由的初始化，此中间件负责执行路由逻辑
 * 
 * @example 可传入多个路由实例 new RouteMiddleware($route, ...)
 */

class RouteMiddleware implements Middleware
{
    /**
     * @var Array 路由配置
     */
    private $stack = [];

    public function __construct(...$routes)
    {
        $stacks = array_map(function ($val) {
            return $val->stack;
        }, $routes);

        $this->stack = array_replace_recursive(...$stacks);
    }

    public function execute($app, $next)
    {
        $stack = $this->stack;
        $url = $app::$req['url'];
        $method = $app::$req['method'];
        
        $arr = explode('/', $url);
        $len = count($arr);

        // 路由匹配，找到对应的路由对象
        for ($i = 0; $i < $len; $i ++) {
            // 路径字符
            $str = $arr[$i];

            if (!$str) continue;

            // NOTE: 引用赋值可解决 Undefined index 错误
            $stack = &$stack['children'];

            // 1. 严格匹配
            if (isset($stack[$str])) {
                $stack = $stack[$str];
                continue;
            }
            // 2. 路径参数匹配
            if (isset($stack['*'])) {
                $param = $stack['*']['param'];
                $stack = $stack['*'];

                $app::$req['params'][$param] = $str;
                continue;
            }
            // 3. 通配符匹配
            if (isset($stack['**'])) {
                $stack = $stack['**'];
                continue;
            }
            
            // 4. 匹配失败，找不到对应路由
            break;
        }

        // 未匹配成功
        if (empty($stack['methods'])) {
            $res = new Response(HttpCode::NOT_FOUND);

            $res->setErrorMsg("找不到对应的 API");
            $res->end();
            return;
        }

        // 匹配成功，但是没有对应的 HTTP 方法
        if (empty($stack['methods'][$method])) {
            $res = new Response(HttpCode::METHOD_NOT_ALLOWED);

            $res->setErrorMsg("禁止使用此 HTTP 方法请求");
            $res->end();
            return;
        }

        // 执行请求逻辑，注入 Request 对象
        $stack['methods'][$method]['controller']($app::$req);
        // 进入下一个中间件
        $next();
    }

    public function fallback($app)
    {
        // Nothing
    }
}
