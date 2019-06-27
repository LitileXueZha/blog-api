<?php

/**
 * 定义了一个 `public` 目录以存放入口文件
 * 
 * 像下面、`src` 等核心文件不能暴露出来，参考了 Laravel 的做法
 */

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/config.php';

require_once __DIR__.'/src/app.php';
require_once __DIR__.'/src/controllers/Article.php';

// 版本号 v1
// 如果之后新开接口，和现有冲突，换个版本号就行
$route = new Route('/v1');

function aa($req) {
    $res = new Response(HttpCode::OK, $req);
    $res->end();
}

$route
    // 文章相关
    ->get('/article', 'aa') // 获取文章列表
    ->post('/article', 'aa') // 创建文章
    ->get('/article/:id', 'aa') // 获取单个文章
    ->put('/article/:id', 'aa') // 更新单个文章
    ->delete('/article/:id', 'aa') // 删除单个文章

    // 其它
    ->get('/no', 'aa')
    ->get('/user', 'aa')
    ->delete('/user', 'aa')
    ->get('/user/:id', 'aa')
    ->get('/example/**', 'aa')
    ->get('/', 'aa');

App::use(new Auth);
App::use(new RouteMiddleware($route));
