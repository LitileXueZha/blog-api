<?php

error_reporting(E_ALL | E_STRICT);

require_once('./vendor/autoload.php');
require_once('./src/kits/request.php');
require_once('./src/kits/response.php');

$HTTP_CODE = require('./src/constants/http_code.php');

class App
{
    private $helo;
    function __construct() {
        $this->helo = 'daf';
        $this-> version = '1.0';
    }

    public function start() {
        $req = (array) new Request();

        $this->req = $req;

        global $HTTP_CODE;

        // echo $a;
        throw new Exception('自定义错误');
        $res = new Response($HTTP_CODE['UNAUTHORIZED']);
        $res->setErrorMsg('鉴权失败');
        $res->end();
        echo json_encode($req, JSON_FORCE_OBJECT);
    }
}

$app = new App();
echo json_encode($app);

// set_error_handler(function ($errno, $errstr, $errfile, $errline) {
//   http_response_code(500);
//   echo json_encode([
//     'handler_type' => 'Error',
//     'code' => $errno,
//     'msg' => $errstr,
//     'file' => $errfile,
//     'line' => $errline,
//   ]);
//   die();
// });

// set_exception_handler(function ($e) {
//   http_response_code(500);
//   // header('content-type: text/html');
//   // echo $e->__toString();
//   echo json_encode([
//     'handler_type' => 'Exception',
//     'code' => $e->getCode(),
//     'msg' => $e->getMessage(),
//     'file' => $e->getFile(),
//     'line' => $e->getLine(),
//     'trace' => $e->getTrace(),
//     'trace_str' => $e->getTraceAsString(),
//   ]);
//   die();
// });

// $a = function () use ($app) {
//   $app->start();
// };

// a();

