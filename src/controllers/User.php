<?php

/**
 * 跟用户相关的逻辑，包括特殊的 API 鉴权
 */

require_once __DIR__.'/../models/User.php';

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

        if (empty($headers['AUTHORIZATION'])) {
            // 没有来源，默认为接口域名
            if (empty($headers['ORIGIN'])) {
                $headers['ORIGIN'] = $_SERVER['REQUEST_SCHEME'] .'://'. $headers['HOST'];
            }

            $ip = $_SERVER['REMOTE_ADDR'];
            $params = [
                'user_ip' => $ip,
                'user_ip_address' => IPSearch::ip138($ip),
                'user_origin' => $headers['ORIGIN'],
                'user_agent' => $headers['USER_AGENT'],
            ];
            // 数据库生成用户
            $res = MMU::add($params);
            $uid = $res['id'];
        } else if ($token = Auth::parse($headers['AUTHORIZATION'])) {
            $uid = $token['uid'];
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

        // 验证密码
        if (!password_verify($data['pwd'], $user['pwd'])) {
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
}
