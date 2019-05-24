<?php

/**
 * 路由
 * 
 * 目前仅支持两种高级模式：
 * 1. /user/**。通配符 **，匹配一切，不包括 /
 * 2. /user/:id。路径参数 :，携带名为 id 的路径参数
 * 
 * 路径参数优先级比通配符高。
 * 
 * @example 可链式调用：(new Route()).get('/').post('/user')
 * 
 * 路由结构
 * 
 * ```php
 * $unit = [
 *  'param'? => '',
 *  'methods' => [
 *      'GET' => ['controller' => Funcion]
 *  ],
 *  'children'? => [
 *      '$url' => $unit,
 *  ],
 * ];
 * ```
 * 
 * 如果路径寻找完成后，没发现 methods 里的 HTTP 方法，可视为未定义此路由，返回 404
 */

class Route
{
    /**
     * @var String 路由前缀
     */
    private $prefix = '/';
    // 路由配置
    public $stack = [];

    /**
     * 初始化
     * 
     * @param String $prefix 路由前缀。默认为 '/'
     */
    function __construct($prefix = '/')
    {
        $this->prefix = $prefix;
    }

    /**
     * 注册路由
     * 
     * @param String $url 路径
     * @param String $method 请求方法
     * @param Function $controller 对应请求逻辑
     */
    private function register($url, $method, $controller)
    {
        $arr = explode('/', $this->prefix . $url);
        $len = count($arr);
        // NOTE: 必须是引用赋值
        $stack = &$this->stack;

        // 将当前路由加入到配置中
        for ($i = 0; $i < $len; $i ++) {
            $str = $arr[$i];
            
            // 过滤空字符串
            if (!$str) continue;

            $stack = &$stack['children'];

            if (strpos($str, ':') === 0) {
                $stack['*']['param'] = substr($str, 1);
                $str = '*';
            }

            $stack = &$stack[$str];
        }

        $stack['methods'][$method]['controller'] = $controller;
    }

    /**
     * 路由方法 OPTIONS
     * 
     * @param String $url 路径名
     * @param String $controller 路由对应逻辑
     * 
     * @return Route
     */
    public function options($url, $controller)
    {
        $this->register($url, 'OPTIONS', $controller);

        return $this;
    }

    /**
     * 路由方法 HEAD
     * 
     * @param String $url 路径名
     * @param String $controller 路由对应逻辑
     * 
     * @return Route
     */
    public function head($url, $controller)
    {
        $this->register($url, 'HEAD', $controller);

        return $this;
    }

    /**
     * 路由方法 GET
     * 
     * @param String $url 路径名
     * @param String $controller 路由对应逻辑
     * 
     * @return Route
     */
    public function get($url, $controller)
    {
        $this->register($url, 'GET', $controller);

        return $this;
    }

    /**
     * 路由方法 POST
     * 
     * @param String $url 路径名
     * @param String $controller 路由对应逻辑
     * 
     * @return Route
     */
    public function post($url, $controller)
    {
        $this->register($url, 'POST', $controller);

        return $this;
    }

    /**
     * 路由方法 PUT
     * 
     * @param String $url 路径名
     * @param String $controller 路由对应逻辑
     * 
     * @return Route
     */
    public function put($url, $controller)
    {
        $this->register($url, 'PUT', $controller);

        return $this;
    }

    /**
     * 路由方法 DELETE
     * 
     * @param String $url 路径名
     * @param String $controller 路由对应逻辑
     * 
     * @return Route
     */
    public function delete($url, $controller)
    {
        $this->register($url, 'DELETE', $controller);

        return $this;
    }
}
