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
 */

class Route
{
    /**
     * @var String 路由前缀
     */
    private $prefix = '';
    // 允许方法
    private $allowMethods = [];
    public $stack = [];

    /**
     * 初始化
     * 
     * @param String $prefix 路由前缀。默认为 ''
     */
    function __construct($prefix = '', $allowMethods = ['options', 'head', 'get', 'post', 'put', 'delete'])
    {
        $this->prefix = $prefix;
        $this->allowMethods = $allowMethods;

        $this->registerMethods();
    }

    /**
     * 注册实例可使用的方法
     * 
     * 默认方法为全部的 options、head、get、post、put、delete
     * 
     * 可在实例化时传入，例如：new Route('', ['get', 'post'])
     */
    private function registerMethods()
    {
        $len = count($this->allowMethods);

        for ($i = 0; $i < $len; $i ++) {
            $method = $this->allowMethods[$i];

            $this->$method = function ($url, $controller) {
                $this->register($url, strtoupper($mehtod), $controller);
                
                return $this;
            };
        }
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
        $arr = split('/', $url);
        $len = count($arr);
        $stack = [];
        $tmpStack = &$stack;

        for ($i = 0; $i < $len; $i ++) {
            $str = $arr[$i];

            if (strpos($str, ':') === 0) {
                // 路径参数
                $stack[$method]['*'] = [
                    'controller' => $controller,
                    'param' => substr($str, 1),
                ];
            } else {
                // 通配符、严格路径
                $stack[$method]['**'] = ['controller' => $controller];
            }

            $tmpStack = $tmpStack['children'];
        }
        
        unset($tmpStack['children']);
    }
}


try {
    $route = new Route();
    // var_dump($route);
    // exit();
    

    var_dump(($route->get)());
    $route->get('/user', 'daf')->post()->delete();
    
    Log::debug($route);
} catch (Throwable $e) {
    var_dump($e);
}