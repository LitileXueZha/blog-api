<?php

/**
 * 访问控制 - 数据模型
 * 
 * 用于权限管理，目前维护到代码中。以后可以放到数据库里
 * 
 * @example
 * ```php
 * ACL::getacl('GET /v1/users', 'tao');
 * ```
 * 
 * @see acl 规则关键字符解释：
 * 
 * 1. `0/1` ACL 中代表**无/有**权限。默认为 `0`
 * 2. `*` URL 中的代表动态匹配
 * 3. `:` ACL 中代表分割 `user_id` 与权限
 * 4. `$` ACL 中代表分割不同用户
 * 5. `*` ACL 中代表所有用户
 * 6. `+` ACL 中代表拼接具体的某项自定义权限
 */

namespace TC\Model;

class AccessControl
{
    /**
     * acl 数据来源
     */
    const DATA_FILE = __DIR__.'/../constants/AccessControlList.json';

    /**
     * 权限列表
     * 
     * @var Array
     */
    private static $acl;

    /**
     * 所有的路由集合正则
     * 
     * @example `/acl1|acl2/`
     */
    private static $reg;

    /**
     * 获取 acl 访问权限列表
     * 
     * @param String $api 请求接口 api
     * @param String $uid 访问用户 id
     * @param String|NULL $privilege 具体的自定义权限
     * @return Number|NULL 权限。`0/1`，为 `NULL` 时表示不存在此 api 权限
     */
    public static function getacl($api, $uid, $privilege = NULL)
    {
        if (!self::$acl) {
            self::init();
        }

        // 使用初始化的路由集合正则定位到具体的某个 acl key
        $res = preg_match(self::$reg, $api, $matches);

        if (!$res)  {
            // 不存在此 api 权限
            return NULL;
        }

        // 为什么是长度 - 2？因为捕获了分组，且第一个为匹配中的所有集合
        $index = count($matches) - 2;
        $acl = self::$acl[$index];

        // 复杂的字符串规则
        if (is_string($acl)) {
            // 转化用户有权限的 acl 正则表达式。直接匹配 acl 规则，
            // 如果匹配到了，说明有权限；没有则无权限
            // 例如 'tao' => '/tao|*:1/'
            $userAclReg = $privilege ? "/$uid|\*\+$privilege\:1/" : "/$uid|\*\:1/";
            // 返回匹配次数 0/1，正好契合权限设计
            $resAcl = preg_match($userAclReg, $acl);

            return $resAcl;
        }

        return $acl;
    }

    /**
     * 初始化
     * 
     * 目前是从本地写好的 `.json` 文件中读取
     */
    private static function init($path = self::DATA_FILE)
    {
        $contents = file_get_contents($path);
        $arr = json_decode($contents, true);
        $regs = [];
        $acls = [];

        // 解析事先配置的 acl
        foreach ($arr as $url => $acl) {
            $reg = str_replace(['/', '*'], ['\\/', '(?:[^\/.#]+)'], $url);
            $acls[] = $acl;
            // 捕获分组信息
            $regs[] = "($reg)";
        }

        $regex = implode('|', $regs);

        self::$reg = "/^$regex$/";
        self::$acl = $acls;
    }
}
