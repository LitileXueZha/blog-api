<?php
  error_reporting(E_ALL | E_STRICT);

  require_once('./vendor/autoload.php');
  require_once('./src/route.php');

  // header('Content-Type: application/json');
  
  class App {
    function __construct() {
      $this-> version = '1.0';
    }

    static function start() {
      phpinfo();
    }
  }

  $app = new App();
  
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
      // 不处理 E_NOTICE 级别的错误
      if ($errno === E_NOTICE) return false;

      http_response_code(500);
      echo json_encode([
        errno => $errno,
        errstr => $errstr,
        errfile => $errfile,
        errline => $errline,
      ]);
      die();
    });

    set_exception_handler(function ($e) {
      echo json_encode([
        msg => $e->getMessage(),
        code => $e->getCode(),
        file => $e->getFile(),
        line => $e->getLine(),
        trace => $e->getTrace(),
        traceStr => $e->getTraceAsString(),
      ]);
      die();
    });

  App::start();

