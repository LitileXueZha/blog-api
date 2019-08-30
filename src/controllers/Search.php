<?php

/**
 * 搜索
 * 
 * 目前只支持搜索文章
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
        // 空格作为分词符。转化为正则
        $qstr = str_replace(' ', '[\s\S]{0,20}|[\s\S]{0,20}', $q);
        $qstr = "[\s\S]{0,20}".$qstr."[\s\S]{0,20}";
        // 匹配正则
        $regex = "/$qstr/iu";
        // $regex = "/(.{0,20}标题.{0,20})/iu";
        
        $rows = MMA::fulltextSearch($q, ['limit' => $limit]);
        $res = new Response(HttpCode::OK, $rows);

        $test = [];
        foreach ($rows['items'] as $v) {
            $summary = $v['summary'] . $v['content'];
            preg_match_all($regex, $summary, $m1);
            $m1str = implode('...', array_map(function ($a) {return trim($a);}, array_unique($m1[0])));
            $test[] = [
                'title' => $v['title'],
                'summary' => $summary,
                'm1' => $m1[0],
                'm1str' => $m1str,
            ];
        }
        Log::debug($test);

        $res->end();
    }

    /**
     * 提取包含关键词的文本片段
     * 
     * 目前使用 `空格` 分词，后续考虑开源的分词工具，例如 jieba。
     * 提取规则为包含关键词片段的前后 20 个字
     * FIXME: 处理多字节字符乏力，mbstring 没有提供相关正则功能
     * 
     * @param String 关键词文本
     * @param String 待提取的长文本
     * @return String|NULL 文本片段
     */
    protected static function keytext($keyword, $longtext)
    {
        // 匹配前后20个字符
        $key = '[\s\S]{0, 20}';
        // 空格作为分词符
        $reg = str_replace(' ', "$key|$key", $keyword);
        $regex = "/$key$reg$key/iu";

        preg_match_all($regex, $longtext, $matches);

        $match = array_map(function ($value) {
            // 去除前后空格
            $value = trim($value);
            // 去除不可读字符
            $value = preg_replace('/[\n\b\t\v]/', '', $value);

            return $value;
        }, $matches[0]);

        // 省略号为连接符
        $text = implode('...', $match);

        return $text;
    }
}
