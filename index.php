<?php
  require_once('./vendor/autoload.php');
  require_once('./src/route.php');

  header('Content-Type: application/json');
  
  class App {
    function __construct() {
      $this-> version = '1.0';
    }

    static function start() {
      echo json_encode($_SERVER);
    }
  }

  $app = new App();

  try {
    echo $app;
  } catch ($e) {
    
  }
