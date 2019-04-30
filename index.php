<?php

require_once('./vendor/autoload.php');
require_once('./config.php');

require_once('./src/kits/request.php');
require_once('./src/kits/response.php');
require_once('./src/kits/error_handler.php');
require_once('./src/constants/http_code.php');


class App
{
    public $version = 'v1';
    private $a = 1;
    protected static $b = 2;
    public $arr = [1,2];
    
    public function __construct()
    {
        ErrorHandler::init();
    }

    public static function setApiVersion($version)
    {
        $this->version = $version;
    }

    public function start()
    {
        
        // self::$b = 3;
        // echo self::$b;
        $req = new Request();
        $this->req = $req;

        global $HTTP_CODE;

        // echo $a;
        $res = new Response(HttpCode::OK, $req);
        $res->setErrorMsg('鉴权失败');
        $res->end();
    }
}

$app = new App();

// $app->start();

$func1 = function ($next) {
    // return $next + 2;
    return function ($a) use ($next) {
        return $next($a + 2);
    };
};
$func2 = function ($next) {
    // return $b * $b;
    return function ($a) use ($next) {
        return $next($a * $a);
    };
};
$count = 0;
function compose(...$funcs) {
    return array_reduce($funcs, function ($f, $g) {
        // var_dump($f, $g);
        // exit();
        // $count++;
        return function (...$args) use ($f, $g) {
            return $f($g(...$args));
        };
    });
}

echo compose($func1, $func2)(function ($a) {return $a;})(2);

$route = new Route('/order');

$route
    ->get('/')
    ->get('/edit')
    ->post('/');

$routes = Route::compose($route, $route1);

App::use($routes);

class AuthMiddleware implements Middleware
{
    function exec($app, $next)
    {
        echo $app;
        $next();
        echo $app;
    }
}

App::use(AuthMiddleware::exec);
