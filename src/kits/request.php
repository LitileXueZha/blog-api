<?php
  class Request {
    function __construct() {
      $this->method = $_SERVER['REQUEST_METHOD'];
      $this->url = $_SERVER['REQUEST_URI'];
      $this->headers = self::getAllHeaders();
      $this->data = self::getData();
    }

    public static function getAllHeaders() {
      $headers = [];

      return $headers;
    }

    public static function getData() {
      $method = $_SERVER['REQUEST_METHOD'];

      // 这三个请求数据置于 url 之后
      if ($method === 'OPTIONS' || $method === 'HEAD' || $method === 'GET') {
        return $_GET;
      }

      $raw = file_get_contents('php://input');

      // POST 请求直接拿 $_POST
      if ($method === 'POST') {
        $data = $_POST;

        // 如果 $_POST 拿不到，得要从 php://input 中拿
        if (!$data) $data = json_decode($raw, true);

        return $data;
      }

      // 其它请求 PUT、DELETE 从 php://input 中获取，必须保证不是 multipart/form-data 形式
      return json_decode($raw, true);
    }
  }
