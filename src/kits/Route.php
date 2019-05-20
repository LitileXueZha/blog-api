<?php

/**
 * 路由
 * 
 * 目前仅支持两种高级模式：
 * 1. /user/*。通配符 *，匹配一切，不包括 /
 * 2. /user/:id。路径参数 :，携带名为 id 的路径参数
 * 
 * @example 可链式调用：(new Route('/')).get().post()
 */

class Route
{
    /**
     * @var String 路由前缀
     */
    private $prefix = '/';

    /**
     * 初始化
     * 
     * @param String $prefix 路由前缀。默认为 '/'
     */
    function __construct($prefix = '/')
    {
        $this->prefix = $prefix;
    }

    public function get($url, $controller)
    {
        $this->config['GET'][$this->prefix . $url] = $controller;

        return $this;
    }

    public function post()
    {
        
    }
}
