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
require_once __DIR__.'/src/controllers/Comment.php';
require_once __DIR__.'/src/controllers/Search.php';

// 版本号 v1
// 如果之后新开接口，和现有冲突，换个版本号就行
$route = new Route('/v1');

function aa($req) {
    $res = new Response(HttpCode::OK, $req);
    $res->end();
}

$route
    // 文章模块
    ->get('/articles', 'Article::list') // 获取文章列表
    ->post('/articles', 'Article::create') // 创建文章
    ->get('/articles/:id', 'Article::read') // 获取单篇文章
    ->put('/articles/:id', 'Article::update') // 更新单篇文章
    ->delete('/articles/:id', 'Article::delete') // 删除单篇文章
    ->get('/articles/trash', 'Article::getTrashList') // 文章垃圾箱

    // 标签模块
    ->get('/tags', 'Tag::list') // 列表
    ->post('/tags', 'Tag::create') // 创建标签
    ->get('/tags/:id', 'Tag::read') // 获取单个标签
    ->put('/tags/:id', 'Tag::update') // 更新单个标签
    ->delete('/tags/:id', 'Tag::delete') // 删除标签

    // 留言模块
    ->get('/msg', 'Msg::list') // 列表
    ->post('/msg', 'Msg::create') // 创建留言
    ->get('/msg/:id', 'Msg::read') // 获取单条留言
    ->put('/msg/:id', 'Msg::update') // 更新单条留言
    ->delete('/msg/:id', 'Msg::delete') // 删除留言

    // 评论模块
    ->get('/comments', 'Comment::list') // 列表
    ->post('/comments', 'Comment::create') // 创建评论
    ->get('/comments/:id', 'Comment::read') // 获取单条评论
    ->put('/comments/:id', 'Comment::update') // 更新单条评论
    ->delete('/comments/:id', 'Comment::delete') // 删除单条评论
    ->get('/comments/all', 'Comment::all') // 管理后台获取全部评论数据

    // 其它
    ->get('/search', 'Search::list') // 搜索
    ->get('/user', 'aa')
    ->delete('/user', 'aa')
    ->get('/user/:id', 'aa')
    ->get('/example/**', 'aa');

App::use(new Auth);
App::use(new RouteMiddleware($route));
