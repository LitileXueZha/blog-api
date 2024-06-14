<?php

/**
 * 鉴权
 * 
 * 1. 跳过 OPTIONS 请求
 * 2. 生成令牌的接口绕过鉴权
 * 3. 在 header 中携带 `Authorization` 参数
 * 4. 令牌校验
 */

class Auth implements Middleware
{
    /**
     * 生成令牌的接口，需要绕过鉴权
     * 
     * @var Array
     */
    const API_OMIT = ['/api/v1/oauth', '/api/v1/user/login'];

    /**
     * 令牌失效时间，单位为天
     * 
     * @var Number
     */
    const API_TOKEN_EXPIRE_DAYS = 3;

    // 令牌 header 信息
    const API_TOKEN_TYPE = 'JWT';
    const API_TOKEN_ALGORITHM = 'HS256';

    // 令牌校验结果
    const TOKEN_ACCESS = 1; // 校验成功
    const TOKEN_FAIL = 2; // 校验失败
    const TOKEN_EXPIRE = 3; // 令牌失效

    public function execute($app, $next)
    {
        $req = $app::$req;
        $method = $req['method'];
        $headers = $req['headers'];
        $url = $req['url'];

        // 1. 跳过 OPTIONS 请求
        if ($method === 'OPTIONS') {
            $res = new Response(HttpCode::NO_CONTENT);
            $res->end();
            exit();
        }

        // 指明服务端渲染的路由跳过
        // 需要 nginx 支持，传递一个请求头 x-private-ssr
        if (isset($_SERVER['X_PRIVATE_SSR'])) {
            // 取消跳转剩下的中间件，直接跳过
            $next();
            return;
        }

        // 2. 生成令牌的接口绕过鉴权
        if (in_array($url, self::API_OMIT)) {
            $next();
            return;
        }

        // 3. 在 header 中携带 `Authorization` 参数
        if (empty($headers['AUTHORIZATION'])) {
            // 未认证
            $res = new Response(HttpCode::UNAUTHORIZED);
            $res->setErrorMsg('未认证');
            $res->end();
            return;
        }

        $tokenStr = $headers['AUTHORIZATION'];
        $token = self::parse($tokenStr);
        $res = self::validate($tokenStr, $token);

        // 4. 令牌校验
        if ($res === self::TOKEN_FAIL) {
            // 签名校验失败
            $res = new Response(HttpCode::UNAUTHORIZED);
            $res->setErrorMsg('认证失败');
            $res->end();
            return;
        } else if ($res === self::TOKEN_EXPIRE) {
            // 失效
            $res = new Response(HttpCode::UNAUTHORIZED);
            $res->setErrorMsg('认证已过期');
            // 设置失效状态码
            $res->resetResCode(ErrorCode::TOKEN_EXPIRED);
            $res->end();
            return;
        }

        // 校验通过，进入下个中间件
        // 设置用户
        $app::$req['AUTH_MIDDLEWARE'] = ['user' => $token['uid']];
        $next();
    }

    public function fallback($app)
    {
        // Nothing.
    }

    /**
     * 生成 jwt 令牌
     * 
     * @param String $uid 用户 id
     * @return String
     */
    public static function generate($uid)
    {
        $header = [
            'typ' => self::API_TOKEN_TYPE,
            'alg' => self::API_TOKEN_ALGORITHM,
        ];
        $exp = time() + (self::API_TOKEN_EXPIRE_DAYS * 24 * 60 * 60);
        $payload = "$exp:$uid";
        $token = self::encrypt($header, $payload)['token'];

        return $token;
    }

    /**
     * 加密 jwt
     * 
     * @param Array 头部
     * @param String 内容
     * @return Array 秘钥部分与完整 jwt
     */
    private static function encrypt($header, $payload)
    {
        $header = base64_encode(json_encode($header));
        $payload = base64_encode($payload);
        // 重要！！！这是加密部分
        // 有提高安全性必要的话可以再改改
        $secret = base64_encode(hash_hmac('sha256', "$header.$payload", API_SECRET, true));

        return [
            'secret' => $secret,
            'token' => "$header.$payload.$secret",
        ];
    }

    /**
     * 校验 jwt 令牌
     * 
     * @param String $tokenStr 令牌字符串
     * @param Array $token 解析的令牌数组
     * @return Boolean
     */
    public static function validate($tokenStr, $token)
    {
        // 解析有误
        if (empty($token)) {
            return self::TOKEN_FAIL;
        }

        $typ = $token['typ'];
        $alg = $token['alg'];

        // 令牌类型不正确
        // if (!($typ === self::API_TOKEN_TYPE && $alg === self::API_TOKEN_ALGORITHM)) {
        //     return self::TOKEN_FAIL;
        // }

        // NOTE: 有可能 payload 里的失效时间不是时间戳
        $exp = (int) $token['exp'];
        $uid = $token['uid'];
        // $secret = self::encrypt(['typ' => $typ, 'alg' => $alg], "$exp:$uid")['secret'];

        // // 令牌验签失败
        // 这一步放到了解析里面，提交安全性
        // if ($secret !== $token['secret']) {
        //     return self::TOKEN_FAIL;
        // }

        // 令牌失效
        if (time() > $exp) {
            return self::TOKEN_EXPIRE;
        }

        return self::TOKEN_ACCESS;
    }

    /**
     * 解析 jwt 令牌
     * 
     * 不是有效的令牌直接返回 `false`，
     * 否则包含 `['typ', 'alg', 'exp', 'uid', 'secret']` 等数据
     * 
     * @param String $token
     * @return Array|Boolean
     */
    public static function parse($token)
    {
        // 去除头部字符
        $token = str_replace('Bearer ', '', $token);
        // 解析 jwt
        $arr = explode('.', $token);
        [$header, $payload, $secret] = array_pad($arr, 3, NULL);

        if (!($header && $payload && $secret)) {
            return false;
        }

        // 解析 header
        $arr = json_decode(base64_decode($header), true) + ['typ' => NULL, 'alg' => NULL];
        $typ = $arr['typ'];
        $alg = $arr['alg'];

        if (!($typ && $alg)) {
            return false;
        }

        // 解析 payload
        $arr = explode(':', base64_decode($payload));
        [$exp, $uid] = array_pad($arr, 2, NULL);

        if (!($exp && $uid)) {
            return false;
        }

        $realToken = self::encrypt(['typ' => $typ, 'alg' => $alg], "$exp:$uid")['secret'];
        
        // 加入安全性验证。否则解析失败
        if ($secret !== $realToken) {
            return false;
        }

        return [
            'typ' => $typ,
            'alg' => $alg,
            'exp' => $exp,
            'uid' => $uid,
            'secret' => $secret,
        ];
    }
}
