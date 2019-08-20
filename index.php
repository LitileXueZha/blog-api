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
require_once __DIR__.'/src/controllers/Tag.php';
require_once __DIR__.'/src/controllers/Msg.php';

// 版本号 v1
// 如果之后新开接口，和现有冲突，换个版本号就行
$route = new Route('/v1');

function aa($req) {
    $res = new Response(HttpCode::OK, $req);
    $res->end();
}

$route
    // 文章相关
    ->get('/articles', 'Article::list') // 获取文章列表
    ->post('/articles', 'Article::create') // 创建文章
    ->get('/articles/:id', 'Article::read') // 获取单个文章
    ->put('/articles/:id', 'Article::update') // 更新单个文章
    ->delete('/articles/:id', 'Article::delete') // 删除单个文章

    // 标签相关
    ->get('/tags', 'Tag::list') // 列表
    ->post('/tags', 'Tag::create') // 创建标签
    ->get('/tags/:id', 'Tag::read') // 获取单个标签
    ->put('/tags/:id', 'Tag::update') // 更新单个标签
    ->delete('/tags/:id', 'Tag::delete') // 删除标签

    // 留言相关
    ->get('/msg', 'Msg::list') // 列表
    ->post('/msg', 'Msg::create') // 创建留言
    ->put('/msg/:id', 'Msg::update') // 更新单条留言
    ->delete('/msg/:id', 'Msg::delete') // 删除留言

    // 其它
    ->get('/no', 'aa')
    ->get('/user', 'aa')
    ->delete('/user', 'aa')
    ->get('/user/:id', 'aa')
    ->get('/example/**', 'aa')
    ->get('/', 'aa');

App::use(new Auth);
App::use(new RouteMiddleware($route));
