<?php

/**
 * 搜索
 * 
 * 目前只支持搜索文章
 */

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
        Log::debug($req);
    }
}
