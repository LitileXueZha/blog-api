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
    'DEBUG' => true,

    'CORS' => 'https://wwww.ningtaostudy.cn',

    'DB_HOST' => 'localhost',
    'DB_PORT' => 3306,
    'DB_NAME' => 'blog',
    'DB_USER' => 'root',
    'DB_PASSWORD' => '123456',

    'DIR_ROOT' => __DIR__,
];

/**
 * 通用设置
 * 
 * + 时区
 * + 自动加载类名
 */
date_default_timezone_set('Asia/Shanghai');
spl_autoload_register(function ($className) {
    $classDir = ['constants', 'interfaces', 'kits'];
    $existClass = "不存在的类：$className";

    for ($i = 0; $i < 3; $i++) {
        $file = "./src/$classDir[$i]/$className.php";

        if (file_exists($file)) {
            $existClass = $file;
            break;
        };
    }

    require_once $existClass;
}, false);

// 定义全局常量
foreach ($config as $key => $value) {
    define($key, $value);
}
