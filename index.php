<?php

require_once('./vendor/autoload.php');
require_once('./config.php');

require_once('./src/app.php');

// 版本号 v1
// 如果之后新开接口，和现有冲突，换个版本号就行
$route = new Route('/v1');
// var_dump($route);
// exit();

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

App::start();
