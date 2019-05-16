<?php

/**
 * 应用
 * 
 * @example 直接 App::start() 即可
 */

class App
{
    private static $middlewares = [];
    public static $req = [];
    
    /**
     * 启动此应用
     */
    public static function start()
    {
        // 1. 异常捕获
        ErrorHandler::init();

        // 2. 构造请求对象
        self::$req = (array) new Request();

        // 3. 应用中间件
        self::applyMiddleware();

        // 4. 默认返回
        echo '你好。';
    }

    /**
     * 使用中间件
     * 
     * + 只有放到 start 之前的中间件才会生效
     * + 必须实现 Middleware 接口，否则报错
     * 
     * @param Middleware 中间件类
     */
    public static function use(Middleware $middleware)
    {
        // 转化中间件 Object -> Function
        // TM 直接用函数行不行？抄 koa 里的
        self::$middlewares[] = function ($next) use ($middleware) {
            return function () use ($next, $middleware) {
                $middleware->execute(__CLASS__, function () use ($next, $middleware) {
                    $next();
                    $middleware->fallback(__CLASS__);
                });
            };
        };
    }

    private static function applyMiddleware()
    {
        $middleware = Util::compose(self::$middlewares);

        $middleware();
    }
}
