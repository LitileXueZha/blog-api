<?php

/**
 * 项目配置
 * 
 * 1. 可在任何地方访问
 * 2. 普通的常量应置于 src/constants 下
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
];

// 定义全局常量
foreach ($config as $key => $value) {
    define($key, $value);
}
