<?php

/**
 * 应用
 * 
 * @example 直接 App::start() 即可
 */

require_once('./kits/request.php');

class App
{
    private static $middlewares = [];
    private static $req = [];
    
    /**
     * 启动此应用
     */
    public static function start($args)
    {
        // 1. 构造请求对象
        self::$req = (array) new Request();
        self::applyMiddleware();
    }

    public static function use(Middleware $middleware)
    {
        // 使用中间件，代码中的中间件可以定义为一个函数
        static::$middlewares[] = $middleware;
    }

    private static function applyMiddleware()
    {
        
    }
}
