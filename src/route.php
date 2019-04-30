<?php

/**
 * 路由
 * 
 * 构建路由配置对象，请求进入时匹配路径与方法，执行对应逻辑
 */

class Route
{
    /**
     * @var String 路由前缀
     */
    private $prefix = '';

    /**
     * 初始化
     * 
     * @param String $prefix 路由前缀。默认为 ''
     */
    function __construct($prefix = '')
    {
        $this->prefix = $prefix;
    }

    public function get($url, $controller)
    {
        $this->config['GET'][$this->prefix . $url] = $controller;

        return $this;
    }

    public function post()
}
