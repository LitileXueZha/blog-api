<?php

/**
 * IP 地理位置查询
 * 
 * 目前的两个数据源：
 * 1. ip138.com - 百度
 * 2. ip.tool.chinaz.com - 必应
 * 
 * 原本的想法是找 ip 库查，但是发现各种各样的库，每个标准都不统一，
 * 因此简单点，直接爬百度等搜索引擎的数据。不过别人的数据慎用之，请
 * 不要参考这样的做法，别人辛苦十年，应有回报之！！！
 * 
 * 说白了就是盗取别人的劳动成果。
 */

class IPSearch
{
    /**
     * ip138 站点提供的数据
     * 
     * 此站点已挂，估计是把我服务器 ip 封了。弃用之
     * 
     * @param String $ip
     * @return String 中文地理位置
     */
    public static function ip138($ip)
    {
        if ($res = self::local($ip)) {
            return $res;
        }

        $url = "http://www.ip138.com/iplookup.asp?ip=$ip&action=2";
        $content = file_get_contents($url);
        // 直接使用会导致乱码，先转一遍
        $content = iconv('gb2312', 'utf-8//IGNORE', $content);

        // preg_match('/<li>本站数据：(.+?)<\/li>/m', $content, $res);
        preg_match('/var ip_result = (\{.+?\});/m', $content, $res);

        if (empty($res[1])) {
            // TODO: 查询异常日志记录 warn
            return NULL;
        }

        $info = json_decode($res[1], true, JSON_UNESCAPED_UNICODE);

        return $info['ASN归属地'];
    }

    /**
     * chinaz 站点提供的数据
     * 
     * @param String $ip
     * @return String 中文地理位置
     */
    public static function chinaz($ip)
    {
        if ($res = self::local($ip)) {
            return $res;
        }

        $url = "http://ip.tool.chinaz.com/$ip";
        $content = file_get_contents($url);
        // $reg = '/<span class="Whwtdhalf w50-0">(.+?)<\/span>/m';
        $reg = '/<span class="Whwtdhalf w30-0 lh24 tl ml80">[\s\S]+?<p>(.+?)<\/p>/m';

        if (preg_match($reg, $content, $res)) {
            return $res[1];
        }

        return;
    }

    /**
     * IPv4 一些特殊地址
     * @see https://en.wikipedia.org/wiki/IPv4#Private_networks
     * 
     * @param String $ip
     * @return String|Boolean 解析结果
     */
    private static function local($ip)
    {
        if (strpos($ip, '127.') === 0) {
            return '本地主机';
        }

        $match = strpos($ip, '10.') === 0
                || strpos($ip, '192.168.') === 0
                || preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])/', $ip);

        if ($match) {
            return '专用网络';
        }

        if (strpos($ip, '0.') === 0) {
            return '本地未知网络';
        }

        // 伪装成浏览器
        // 目前只想到 userAgent，可能还有其它判断方式
        ini_set('user_agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.87 Safari/537.36');
    }
}
