<?php
  class App {
    public static $middleware = [];
    public static $req = [];

    function __construct($args) {
      // 初始化一些东西
    }

    public static function start($args) {
      // 启动此应用
    }

    public static function use($middleware) {
      // 使用中间件，代码中的中间件可以定义为一个函数
      static::$middleware[] = $middleware;
    }
  }
