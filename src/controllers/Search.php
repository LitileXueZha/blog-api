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
        $qstr = str_replace(' ', '|', $q);
        // 匹配正则
        $regex = "/($qstr)/iu";
        
        $rows = MMA::fulltextSearch($q, ['limit' => $limit]);
        $res = new Response(HttpCode::OK, $rows);

        $test = [];
        foreach ($rows['items'] as $v) {
            $summary = $v['summary'] .'...'. $v['content'];
            preg_match_all($regex, $summary, $m1, PREG_OFFSET_CAPTURE);
            $test[] = [
                'title' => $v['title'],
                'summary' => $summary,
                'm1' => $m1,
            ];
        }
        Log::debug($test);

        $res->end();
    }
}
