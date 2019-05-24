<?php

/**
 * 格式化请求对象
 */

class Request
{
    public $url;
    public $method;
    public $headers;
    public $data;

    public function __construct()
    {
        $this->url = self::getUrl();
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->headers = self::getAllHeaders();
        $this->data = self::getData();
    }

    /**
     * 返回本次请求 url
     * 
     * @return String
     */
    public static function getUrl()
    {
        // 去掉 ? 之后的字符，并转化成小写
        return strtolower(preg_replace('/(\??|\?.*)$/', '', $_SERVER['REQUEST_URI']));
    }

    /**
     * 返回本次请求所有 headers
     * 
     * @return Array
     */
    public static function getAllHeaders()
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') !== 0 ) continue;

            // 处理 HTTP 头
            $key = str_replace('HTTP_', '', $key);
            // $key = strtolower($key);
            $headers[$key] = $value;
        }

        return $headers;
    }

    /**
     * 返回本次请求指定 header
     * 
     * @param String $header 请求头名称
     * @example 'Content-Type'
     * 
     * @return String|NULL
     */
    public static function getHeader($header)
    {
        $header = strtoupper($header);
        $header = str_replace('-', '_', $header);

        return $_SERVER["HTTP_$header"];
    }

    /**
     * 返回本次请求数据
     * 
     * @return Array
     */
    public static function getData()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // 这三个请求数据置于 url 之后
        if ($method === 'OPTIONS' || $method === 'HEAD' || $method === 'GET') {
            return $_GET;
        }
        
        // POST 请求直接拿 $_POST
        if ($method === 'POST') {
            $data = $_POST;
            
            // 如果 $_POST 拿不到，得要从 php://input 中拿
            if (!$data) $data = json_decode(file_get_contents('php://input'), true);

            return $data;
        }
        
        // 其它请求 PUT、DELETE 从 php://input 中获取，必须保证不是 multipart/form-data 形式
        return json_decode(file_get_contents('php://input'), true);
    }
}
