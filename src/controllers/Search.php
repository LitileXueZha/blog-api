<?php

/**
 * 搜索
 * 
 * 目前只支持搜索文章
 * TODO: 现在直接搜索数据，后续考虑使用 Redis 做缓存
 */

require_once __DIR__.'/../models/Article.php';

use TC\Model\Article as MMA;

class Search extends BaseController
{
    /**
     * 一系列的搜索记录
     * 
     * @param Array 请求信息
     */
    public static function list($req)
    {
        $params = $req['data'];

        if (empty($params['q'])) {
            self::bad('搜索参数不正确');
            return;
        }

        // 搜索参数，去除前后空格
        $q = trim($params['q']);

        if (mb_strlen($q) < 2) {
            self::bad('搜索参数至少为2个字');
            return;
        }

        $limit = self::getLimitByQuery($params);
        // 文章模板检索
        $rows = MMA::fulltextSearch($q, ['limit' => $limit]);
        $total = $rows['total'];
        $searchRows = []; // 搜索结果
        // 分词
        $keyword = self::participle($q);

        foreach ($rows['items'] as $row) {
            $summary = $row['summary'] . $row['text_content'];
            // 用分词精确匹配
            $result = self::keytext($keyword, $summary);

            // NOTE: 只返回精确匹配到的数据
            // FIXME: 分页会有问题！只是简单的筛除，数据库分页对不上了。考虑用 redis 缓存搜索结果
            if (!$result && !preg_match($keyword, $row['title'])) {
                $total --;
                continue;
            }

            // 拼接 url，目前只有 文章
            $row['url'] = '/articles/'. $row['id'];
            // 数据转化
            $row['summary'] = $result ?: $row['summary'] ?: $row['text_content'];
            unset($row['text_content']);

            $searchRows[] = $row;
        }

        $res = new Response(HttpCode::OK, [
            'total' => $total,
            'items' => $searchRows,
        ]);

        $res->end();
    }

    /**
     * 分词
     * 
     * 目前使用 `空格` 分词，后续考虑开源的分词工具，例如 jieba。
     * 
     * @param String 待分词文本
     * @return String 正则表达式
     */
    protected static function participle($input)
    {
        // 转义正则
        $input = preg_quote($input, '/');
        // 匹配前后20个字符
        $key = '[\s\S]{0,20}';
        // 空格作为分词符
        $reg = str_replace(' ', "$key|$key", $input);
        $regex = "/$key$reg$key/iu";

        return $regex;
    }

    /**
     * 提取包含关键词的文本片段
     * 
     * 提取规则为包含关键词片段的前后 20 个字
     * FIXME: 处理多字节字符乏力，mbstring 没有提供相关正则功能
     * 
     * @param String 关键词文本
     * @param String 待提取的长文本
     * @return String|NULL 文本片段
     */
    protected static function keytext($regex, $longtext)
    {
        preg_match_all($regex, $longtext, $matches);

        $match = array_map(function ($value) {
            // 去除前后空格
            $value = trim($value);
            // 去除不可读字符
            $value = preg_replace('/[\n\b\t\v]/u', '', $value);

            return $value;
        }, $matches[0]);

        // 省略号为连接符
        $text = implode('...', array_unique($match));

        return $text;
    }
}
