<?php

/**
 * 项目配置
 * 
 * 1. 可在任何地方访问
 * 2. 普通的常量应置于 src/constants 下
 * 
 * 3. 通用设置。如时区等等...
 */

$config = [
    /**
     * 生产环境：将错误写入日志文件
     */
    'ENV' => 'production',
    // 将错误输出到浏览器。
    // NOTE: 线上必须设置为 false！！！
    'DEBUG' => false,

    'CORS' => 'https://www.ningtaostudy.cn',

    'DB_HOST' => 'localhost',
    'DB_PORT' => 3306,
    'DB_NAME' => 'blog',
    'DB_USER' => 'root',
    'DB_PASSWORD' => '123456',

    'DIR_ROOT' => __DIR__,

    // 接口加密秘钥
    'API_SECRET' => 'Mr.tao is handsome!',

    // 管理员 id
    'ADMIN' => '__ADMIN__',

    // 服务端渲染模板来源
    'SSR_SOURCE' => 'C:/Users/tao/Desktop/Workspace/blog/dist',
];

/**
 * 通用设置
 * 
 * + 时区
 * + 自动加载类名
 */
date_default_timezone_set('Asia/Shanghai');
spl_autoload_register(function ($className) {
    $classDir = ['constants', 'interfaces', 'kits', 'middleware'];

    for ($i = 0; $i < 4; $i++) {
        $file = __DIR__."/src/$classDir[$i]/$className.php";

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
}, false);

// 定义全局常量
foreach ($config as $key => $value) {
    define($key, $value);
}
