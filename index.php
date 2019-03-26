<?php
  error_reporting(E_ALL | E_STRICT);

  require_once('./vendor/autoload.php');
  require_once('./src/kits/request.php');
  require_once('./src/kits/response.php');

  header('Content-Type: application/json');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: OPTIONS,HEAD,GET,POST,PUT,DELETE');
  header('Access-Control-Allow-Headers: *');

  $HTTP_CODE = require('./src/constants/http_code.php');
  
  class App {
    function __construct() {
      $this-> version = '1.0';
    }

    public function start() {
      $req = (array) new Request();

      $this->req = $req;

      global $HTTP_CODE;

      $res = new Response($HTTP_CODE['UNAUTHORIZED']);
      $res->setErrorMsg('鉴权失败');
      $res->end();
      // echo json_encode($req, JSON_FORCE_OBJECT);
    }
  }

  $app = new App();
  
  set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
      'errno' => $errno,
      'errstr' => $errstr,
      'errfile' => $errfile,
      'errline' => $errline,
    ]);
    die();
  });

  set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode([
      'msg' => $e->getMessage(),
      'code' => $e->getCode(),
      'file' => $e->getFile(),
      'line' => $e->getLine(),
      'trace' => $e->getTrace(),
      'trace_str' => $e->getTraceAsString(),
    ]);
    die();
  });

  $app->start();

