<?php

/**
 * 服务端渲染逻辑
 */

use TC\Model\Article as MMA;

class SSR extends BaseController {
    /** 文章模板页 */
    const ARTICLE = '/articles/detail.html';

    /**
     * 渲染文章详情页
     * 
     * @param array 请求信息
     */
    public static function renderArticle($req)
    {
        $id = $req['params']['id'];
        // 筛选线上文章
        $params = [
            'article_id' => $id,
            '_d' => 0,
            'status' => 1,
        ];
        $filename = SSR_SOURCE . self::ARTICLE;

        // 模板文件有误
        if (!file_exists($filename)) {
            http_response_code(500);
            echo '模板资源不存在。';
            Log::error(
                '运行错误',
                '服务端渲染找不到模板资源',
                __FILE__,
                __LINE__,
                'at '. __FILE__ .'('. __LINE__ .')'
            );
            exit();
        }

        $res = MMA::get($params);
        $rawFile = file_get_contents($filename);

        // 当查询到该文章时，处理 html 片段
        if ($res['total'] > 0) {
            $row = $res['items'][0];
            $textContent = htmlspecialchars($row['text_content']);
            // 减小体积
            unset($row['text_content']);
            $data = json_encode($row, JSON_UNESCAPED_UNICODE);

            // 隐藏内容
            $html = "<div style='white-space:pre;width:770px;height:300px;position:absolute;left:-100%;overflow:scroll;'>"
                ."<h1>{$row['title']}</h1>"
                ."<p>$textContent</p>"
                ."<script>__SSR_DATA__=$data;</script>"
                ."</div>";
            
            // TODO: 简单地注入
            // 这只能说是服务端注入，而不是服务端渲染。
            // 主要是执行环境的问题，php 下输出的 html 可能和 js 渲染不一致，
            // 目前还是简单地附加内容到 html 里，专为 seo。另外再注入数据
            $rawFile = str_replace("<!-- %ssr_inject% -->", $html, $rawFile);
            // 添加文档标题
            $rawFile = preg_replace('/<title>.*?<\/title>/', "<title>{$row['title']}_滔's 博客</title>", $rawFile);
        }

        echo $rawFile;
        exit();
    }
}
