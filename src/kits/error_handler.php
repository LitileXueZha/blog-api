<?php
  require_once('./response.php');

  class ErrorHander {
    public static $type = ['Error', 'Exception'];

    public static function init($tyoe) {
      return new Response();
    }

    public static function error() {
      
    }
  }

  set_error_handler(function ($errno, $errstr, $errfile, $errline) {
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

