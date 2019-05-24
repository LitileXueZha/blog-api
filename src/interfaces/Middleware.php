<?php

/**
 * 中间件
 * 
 * 采用了 koajs 的”洋葱模型“
 * 
 * 抽离整个 app 的控制逻辑，模块化形式构建
 */

interface Middleware
{
    /**
     * 中间件逻辑
     * 
     * @param App $app 整个应用对象
     * @param Function $next 执行下个中间件
     */
    public function execute($app, $next);

    /**
     * ”洋葱模型“ 后置逻辑
     * 
     * @param App $app 整个应用对象
     */
    public function fallback($app);
}
