<?php

/**
 * 应用
 * 
 * @example 直接 App::start() 即可
 */

require_once(DIR_ROOT .'/src/kits/error_handler.php');
require_once(DIR_ROOT .'/src/kits/log.php');
require_once(DIR_ROOT .'/src/kits/request.php');
require_once(DIR_ROOT .'/src/kits/response.php');

class App
{
    private static $middlewares = [];
    private static $req = [];
    
    /**
     * 启动此应用
     */
    public static function start()
    {
        // 1. 异常捕获
        ErrorHandler::init();

        // 2. 构造请求对象
        self::$req = (array) new Request();
        self::applyMiddleware();
        Log::debug(self::$req);
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
