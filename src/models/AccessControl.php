<?php

/**
 * 访问控制 - 数据模型
 * 
 * 用于权限管理，目前维护到代码中。以后可以放到数据库里
 */

namespace TC\Modal;

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
     * 当前校验的自定义权限
     */
    private static $privilege;

    /**
     * 获取 acl 访问权限列表
     * 
     * @return Array
     */
    public static function getacl()
    {
        if (!self::$acl) {
            self::init();
        }

        return self::$acl;
    }

    /**
     * 初始化
     * 
     * 目前是从本地写好的 `.json` 文件中读取
     */
    private static function init()
    {
        $contents = file_get_contents(self::DATA_FILE);
        $arr = json_decode($contents, true);
        $acls = [];

        // 解析事先配置的 acl
        foreach ($arr as $url => $acl) {
            $reg = str_replace('/', '\\/', $url);
        }
    }
}
