<?php

/**
 * 常用函数类
 * 
 * @example 使用时直接调用对应函数：Util::compose()
 */

final class Util
{
    /**
     * 中间件合成
     * 
     * 合成下面函数，递归调用，形成"洋葱"模型
     * ```
     * function ($next) {
     *     return function () use ($next) {
     *         Middleware::execute();
     *         $next();
     *         Middleware::fallback();
     *     };
     * }
     * ```
     * 
     * @param Array $middlewares 中间件函数数组
     * @return Function 合成后的中间件函数
     */
    public static function compose($middlewares)
    {
        $len = count($middlewares);
        // new Closure 会报错
        $anonymous = function () {};

        if ($len === 0) return $anonymous;

        if ($len === 1) {
            return $middlewares[0]($anonymous);
        }
        
        return array_reduce($middlewares, function ($f, $g) {
            // PHP 中 reduce 函数第三个入参为 null
            if (is_null($f)) return $g;

            return function ($next) use ($f, $g) {
                return $f($g($next));
            };
        })($anonymous);
    }
}
