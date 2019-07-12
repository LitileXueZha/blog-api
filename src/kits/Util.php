<?php

/**
 * 常用函数类
 * 
 * @example 使用时直接调用对应函数：Util::compose()
 */

final class Util
{
    /**
     * 数据校验的默认规则
     * 
     * @var Array
     */
    protected static $validateRule = [
        'type' => 'string',
        'required' => false,
        'error' => '数据格式不正确',
    ];

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
        $anonymous = function () {/** empty */};

        if ($len === 0) {
            return $anonymous;
        }

        if ($len === 1) {
            return $middlewares[0]($anonymous);
        }
        
        return array_reduce($middlewares, function ($f, $g) {
            // PHP 中 reduce 函数第三个入参为 null
            if (is_null($f)) {
                return $g;
            }

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
     * @return String|NULL 错误提示。全部校验成功则为 NULL
     */
    public static function validate($source, $rules)
    {
        foreach ($rules as $key => $value) {
            if (isset($value['type'])) {
                // 单个规则转化成多规则形式
                $value = [$value];
            }

            foreach ($value as $val) {
                // 默认规则设置
                $rule = array_merge(self::$validateRule, $val);

                $type = $rule['type'];
                $required = $rule['required'];
                $error = $rule['error'];

                $isEmpty = !array_key_exists($key, $source);

                // required 必填校验
                if ($required && $isEmpty) {
                    return $error;
                }

                // 无数据且 required 为 false，不进行校验
                if ($isEmpty) continue;

                $data = $source[$key];

                // type 校验。只做了基本类型
                switch ($type) {
                    case 'string':
                        if (is_string($data)) {
                            break;
                        }

                        return $error;
                    case 'number':
                        if (is_numeric($data)) {
                            break;
                        }

                        return $error;
                    case 'bool':
                        if (is_bool($data)) {
                            break;
                        }

                        return $error;
                    case 'enum':
                        if (in_array($data, $rule['enum'])) {
                            break;
                        }

                        return $error;
                    case 'array':
                        if (is_array($data)) {
                            break;
                        }

                        return $error;
                    case 'email':
                        if (filter_var($data, FILTER_VALIDATE_EMAIL)) {
                            break;
                        }

                        return $error;
                    case 'url':
                        if (filter_var($data, FILTER_VALIDATE_URL)) {
                            break;
                        }

                        return $error;
                }

                // pattern 正则校验
                if (isset($rule['pattern'])) {
                    $count = preg_match($rule['pattern'], $data);

                    if ($count === 0) return $error;
                }

                // validator 自定义函数校验
                if (isset($rule['validator'])) {
                    $result = $rule['validator']($data);

                    if ($result) return $result;
                }
            }
        }
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
