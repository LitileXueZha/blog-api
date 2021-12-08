<?php

/**
 * 跟用户相关的逻辑，包括特殊的 API 鉴权
 */

use TC\Model\User as MMU;

class User extends BaseController
{
    /**
     * 生成 token 接口
     */
    public static function oauth($req)
    {
        $headers = $req['headers'];
        $uid = '';
        // 数据库创建用户
        $createUser = function () use ($headers) {
            // 没有来源，默认为接口域名
            if (empty($headers['ORIGIN'])) {
                $headers['ORIGIN'] = $_SERVER['REQUEST_SCHEME'] .'://'. $headers['HOST'];
            }

            $ip = $_SERVER['REMOTE_ADDR'];
            $params = [
                'user_ip' => $ip,
                'user_ip_address' => IPSearch::chinaz($ip),
                'user_origin' => $headers['ORIGIN'],
                'user_agent' => $headers['USER_AGENT'],
            ];
            // 数据库生成用户
            $res = MMU::add($params);

            return $res['id'];
        };

        if (empty($headers['AUTHORIZATION'])) {
            $uid = $createUser();
        } else if ($token = Auth::parse($headers['AUTHORIZATION'])) {
            $uid = $token['uid'];
        } else {
            // 前端传了 token，但是解析有问题。还是要创建用户
            $uid = $createUser();
        }

        $tokenStr = Auth::generate($uid);
        $res = new Response(HttpCode::OK, $tokenStr);

        $res->end();
    }

    /**
     * 管理用户登录接口
     */
    public static function login($req)
    {
        $data = $req['data'];
        $rules = [
            'account' => [
                'type' => 'string',
                'required' => true,
                'error' => '账号不正确',
            ],
            'pwd' => [
                'type' => 'string',
                'required' => true,
                'error' => '密码不正确',
            ],
        ];

        $msg = Util::validate($data, $rules);

        // 规则校验失败
        if ($msg) {
            self::bad($msg);
            return;
        }

        // 查询字段
        $keys = ['account'];
        $params = Util::filter($data, $keys);

        // 筛选未删除用户
        $params['_d'] = 0;
        $rows = MMU::get($params);

        if ($rows['total'] === 0) {
            self::bad('账号不存在');
            return;
        }

        $user = $rows['items'][0];
        $decryptPwd = Util::xorEncrypt(base64_decode($data['pwd']), $data['account'].date('j'));

        // 验证密码
        if (!password_verify($decryptPwd, $user['pwd'])) {
            self::bad('密码错误');
            return;
        }

        // 删除密码返回
        unset($user['pwd']);

        // 生成 token
        $tokenStr = Auth::generate($user['id']);

        $res = new Response(HttpCode::OK, [
            'token' => $tokenStr,
            'user' => $user,
        ]);

        $res->end();
    }

    /**
     * 查询当前访问用户
     * 
     * 原本思考将 `token` 存入前端，达到免登录，但是用户信息
     * 却丢了，再存入用户数据有安全隐患。看了下 Github 等做法，
     * 需要服务端支持，即 ssr。博客项目完全是前后端分离，发现
     * MDN 做法就是提供了这样一个接口。。。
     */
    public static function whoami($req)
    {
        // 从认证的中间件数据拿
        $userId = $req['AUTH_MIDDLEWARE']['user'];

        $rows = MMU::get(['user_id' => $userId, '_d' => 0]);

        // 有可能数据库没这个用户
        if ($rows['total'] === 0) {
            self::notFound();
            return;
        }
        
        $user = $rows['items'][0];

        unset($user['pwd']);

        $res = new Response(HttpCode::OK, $user);

        $res->end();
    }
}
