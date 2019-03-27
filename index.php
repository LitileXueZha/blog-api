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
        $re = new Request();
        throw new Exception('自定义');
        $this->req = $req;

        global $HTTP_CODE;

        // echo $a;
        $res = new Response(HttpCode::OK, $req);
        $res->setErrorMsg('鉴权失败');
        $res->end();
    }
}

$app = new App();

$app->start();
