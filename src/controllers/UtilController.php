<?php

/**
 * 通用，多模块结合数据
 * 
 * 比如：单个接口包含了文章、标签、留言等数据
 */

require_once __DIR__.'/../models/Article.php';
require_once __DIR__.'/../models/Tag.php';

use TC\Model\Article as MMA;
use TC\Model\Tag as MMT;

class UtilController extends BaseController
{
    /**
     * 获取首页数据
     * 
     * + 最新的 3 篇文章
     * + 所有的标签
     * + 最热的 10 篇文章（只有标题）
     * 
     * @param Array 请求对象
     */
    public static function getIndexData($req)
    {
        $opts = ['orderBy' => 'publish_at DESC, create_at DESC'];
        $articles = MMA::get(['status' => 1, '_d' => 0], $opts)['items'];
        $tags = MMT::get(['status' => 1, '_d' => 0])['items'];

        $resolveArticles = [];
        $topics = [];

        foreach ($articles as $index => $article) {
            // TODO: 根据热度计算
            $topics[] = [
                'id' => $article['id'],
                'title' => $article['title'],
            ];

            // 首页仅展示 3 篇文章
            if ($index < 3) {
                // 文章摘要为空时，返回部分文章内容
                if (empty($article['summary']) && !empty($article['text_content'])) {
                    $article['summary'] = mb_substr($article['text_content'], 0, 160) ."...";
                }

                // 除去文章正文
                $resolveArticles[] = [
                    'id' => $article['id'],
                    'title' => $article['title'],
                    'summary' => $article['summary'],
                    'category' => $article['category'],
                    'tag' => $article['tag'],
                    'tag_name' => $article['tag_name'],
                    'bg' => $article['bg'],
                    'create_at' => $article['create_at'],
                ];
            }
        }

        $res = new Response(HttpCode::OK, [
            'articles' => $resolveArticles,
            'tags' => $tags,
            'topics' => $topics,
        ]);

        $res->end();
    }
}
