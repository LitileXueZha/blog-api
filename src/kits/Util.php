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

    /**
     * 数据格式校验
     * 
     * @param Array 数据
     * @param Array 校验规则
     * @return String 错误提示。全部校验成功则为空
     */
    public static function validate($data, $rules)
    {
        // 循环校验
        foreach ($data as $key => $value) {
            $rule = $rules[$key];
            $type = $rule['type'];
            // TODO:
        }

        // 全部校验成功
        return '';
    }

    /**
     * 短链 id 生成
     * 
     * 采用 62 进制 (a-zA-Z0-9)，将数字转成对应字符串
     * 
     * @example 11 => a2dsax
     * 
     * @param Number 数字
     */
    public static function shortId($num)
    {
        // 62 进制
        $hex = [ 
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
            'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            0, 1, 2, 3, 4, 5, 6, 7, 8, 9,
        ];
        $len = count($hex);
        // 短链 id
        $id = '';

        do {
            $index = $num % $len;
            $left = intval($num / $len);

            // 还能继续被整除，取能被整除的余数
            if ($left !== 0) {
                $index = $num % $len;
            }

            // 拼接
            $id = $hex[$index].$id;
            $num = $left;
        } while ($num > 0);

        return $id;
    }
}
