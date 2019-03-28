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
    'ENV' => 'production',
    'DEBUG' => false,

    'CORS' => 'https://wwww.ningtaostudy.cn',

    'DB_HOST' => 'localhost',
    'DB_PORT' => 3306,
    'DB_NAME' => 'blog',
    'DB_USER' => 'root',
    'DB_PASSWORD' => '123456',

    'DIR_ROOT' => __DIR__,
];

// 通用设置
date_default_timezone_set('Asia/Shanghai');


// 定义全局常量
foreach ($config as $key => $value) {
    define($key, $value);
}
