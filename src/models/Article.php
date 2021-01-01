<?php

/**
 * 文章 - 数据模型
 */

namespace TC\Model;

require_once __DIR__.'/DB.php';

use DBStatement;

class Article
{
    /**
     * 数据库表名称
     * 
     * @var String
     */
    const NAME = 'article';

    /**
     * 添加一条文章
     * 
     * @param Array 文章数据
     * @return Array 插入成功后的记录
     */
    public static function add($data)
    {
        $db = DB::init();
        $tb = self::NAME;

        // 唯一 id 生成
        $data['article_id'] = DB::shortId();
        $columns = array_keys($data);
        $dbs = new DBStatement($tb);

        // dbs 操作
        $dbs->insert($columns);
        
        $statement = $dbs->toString();
        $sql = $db->prepare($statement);

        // 绑定参数
        foreach ($data as $key => $value) {
            $sql->bindValue(":$key", $value);
        }

        // 插入数据
        $sql->execute();

        $res = static::get(['article_id' => $data['article_id']]);

        return $res['items'][0];
    }

    /**
     * 获取文章
     * 
     * @param Array 查询条件
     * @param Array 额外参数。例如 LIMIT、GROUP BY
     * @return Array 文章数据。格式为 [ total, items ]
     */
    public static function get($params, $options = [])
    {
        $db = DB::init();
        $tb = self::NAME;
        $tbJoin = 'tag';
        // 分页
        [
            'limit' => $limit,
            'orderBy' => $orderBy,
        ] = DB::getOptsOrDefault($options);
        $columns = [];
        
        // 列名转化成 dbs 需要的 where 参数
        foreach ($params as $key => $param) {
            if (is_array($param)) {
                // 复杂的 sql 数组型参数查询
                // 吐血的 PDO 不支持直接 IN (1,2,3) 形式，只能一个个绑定
                // @link https://stackoverflow.com/questions/14767530/php-using-pdo-with-in-clause-array
                $columns["$tb.$key"] = count($param);
                continue;
            }

            $columns[] = "$tb.$key";
        }

        $dbs = new DBStatement($tb, $tbJoin);

        // dbs 查询
        $dbs->select(
                "$tb.article_id as id, $tb.title, $tb.summary, $tb.content, $tb.tag",
                "$tbJoin.display_name as tag_name",
                "$tb.text_content, $tb.status, $tb.category, $tb.bg",
                "$tb.publish_at, $tb.create_at"
            )
            ->on("$tb.tag", "$tbJoin.name")
            ->where($columns)
            ->limit($limit)
            ->orderBy($orderBy);

        $statement = $dbs->toString();
        // 转化 >、< 规则使之合法
        // TODO: 放到 dbs 中，包括下面的 bindValue
        $statement = preg_replace(['/:(\w+)>/', '/:(\w+)</'], [':$1Lt', ':$1Gt'], $statement);
        $sql = $db->prepare($statement);

        // 绑定参数
        foreach ($params as $key => $param) {
            if (is_array($param)) {
                // 复杂的 sql 数组型参数查询
                // 这里应该参考 DBStatement 中 where 的转化
                foreach ($param as $i => $val) {
                    $sql->bindValue(":$key$i", $val);
                }
                
                continue;
            }

            $key = str_replace(['>', '<'], ['Lt', 'Gt'], $key);
            $sql->bindValue(":$key", $param);
        }

        $sql->execute();

        $res = $sql->fetchAll();
        $sqlCount = $db->query("SELECT FOUND_ROWS()");
        $count = $sqlCount->fetch();

        return [
            'total' => $count['FOUND_ROWS()'],
            'items' => $res,
        ];
    }

    /**
     * 更新文章
     * 
     * @param String 文章 id
     * @param Array 需要更新的数据
     * @return Array 更新后的记录
     */
    public static function set($id, $data)
    {
        $db = DB::init();
        $tb = self::NAME;

        $columns = array_keys($data);
        $dbs = new DBStatement($tb);

        // dbs 操作
        $dbs->update($columns)
            // 筛选未逻辑删除
            ->where(['__WHERE__' => 'article_id=:id AND _d=0']);

        $statement = $dbs->toString();
        $sql = $db->prepare($statement);

        foreach ($data as $key => $value) {
            $sql->bindValue(":$key", $value);
        }
        // 防止 id 被 sql 注入
        $sql->bindValue(':id', $id);

        $sql->execute();

        // 更新完后去查最新的数据，如果为空，那么此条数据不存在
        $res = static::get(['article_id' => $id, '_d' => 0]);

        return $res['items'];
    }

    /**
     * 删除文章
     * 
     * @param String 文章 id
     * @return Number 受影响的行数。可通过此来判断是否存在此文章
     */
    public static function delete($id)
    {
        $db = DB::init();
        $tb = self::NAME;
        // 转义，防止 sql 注入
        $id = $db->quote($id);
        $statement = "UPDATE $tb SET _d=1 WHERE article_id=$id";

        $count = $db->exec($statement);

        return $count;
    }

    /**
     * 全文本搜索
     * 
     * @param String 查询字符串
     * @param Array 额外的参数。例如 LIMIT
     */
    public static function fulltextSearch($q, $options = [])
    {
        $db = DB::init();
        $tb = self::NAME;

        // 分页
        $limit = DB::getOptsOrDefault($options)['limit'];
        // 防 sql 注入，转义之
        $q = $db->quote($q);
        $dbs = new DBStatement($tb);

        // dbs 查询
        // 只需要查询文本搜索的几个字段，并添加一列 type 固定值为 article
        $dbs->select(
                "article_id as id, title, summary, text_content",
                "'article' as type, publish_at, create_at"
            )
            ->where(['__WHERE__' => "status = 1 AND _d=0 AND match(title, summary, text_content) against($q)"])
            ->limit($limit);

        // 默认 IN NATURAL LANGUAGE MODE
        $statement = $dbs->toString();

        $sql = $db->query($statement);
        $sqlCount = $db->query("SELECT FOUND_ROWS()");
        $res = $sql->fetchAll();
        $resCount = $sqlCount->fetch();

        return [
            'total' => $resCount['FOUND_ROWS()'],
            'items' => $res,
        ];
    }

    /**
     * 获取文章垃圾箱
     * 
     * @param Array 查询条件
     * @param Array 额外参数。例如 LIMIT、GROUP BY
     * @return Array 文章数据。格式为 [ total, items ]
     */
    public static function trash($params, $options = [])
    {
        $db = DB::init();
        $tb = self::NAME;
        // 分页
        [
            'limit' => $limit,
            'orderBy' => $orderBy,
        ] = DB::getOptsOrDefault($options);
        $columns = array_keys($params);
        $dbs = new DBStatement($tb);

        // dbs 查询
        $dbs->select('article_id as id, title, summary, text_content, modify_at')
            ->where($columns)
            ->orderBy($orderBy)
            ->limit($limit);

        $statement = $dbs->toString();
        $sql = $db->prepare($statement);

        foreach ($params as $key => $value) {
            $sql->bindValue(":$key", $value);
        }

        $sql->execute();

        $res = $sql->fetchAll();
        $sqlCount = $db->query("SELECT FOUND_ROWS()");
        $count = $sqlCount->fetch();

        return [
            'total' => $count['FOUND_ROWS()'],
            'items' => $res,
        ];
    }

    /**
     * 获取前后相邻 2 条已上线记录
     * 
     * @param string 当前文章 id
     * @param string 排序字段。默认为 'id'
     * @return Array 文章数据。格式为 [ previous, next ]，可能为 null
     */
    public static function getSiblings($id, $orderCol = 'id')
    {
        $db = DB::init();
        $tb = self::NAME;

        $with = "WITH
            cte_a AS (
                SELECT id, article_id, title, publish_at, create_at
                FROM $tb
                WHERE `status`=1 AND `_d`=0
            ),
            cte_r AS (SELECT $orderCol FROM $tb WHERE `article_id`=:id)";
        $prev = "SELECT article_id AS id, title
            FROM cte_a
            WHERE $orderCol < (SELECT $orderCol FROM cte_r)
            ORDER BY $orderCol DESC, create_at DESC
            LIMIT 1";
        $current = "SELECT NULL AS id, NULL AS title";
        $next = "SELECT article_id AS id, title
            FROM cte_a
            WHERE $orderCol > (SELECT $orderCol FROM cte_r)
            LIMIT 1";
        $statement = "$with ($prev) UNION ($current) UNION ($next)";
        $sql = $db->prepare($statement);

        $sql->bindValue(':id', $id);
        $sql->execute();

        $res = $sql->fetchAll();

        if (count($res) > 2) {
            $prevRow = $res[0];
            $nextRow = $res[2];
        } else {
            $prevRow = is_null($res[0]['id']) ? null : $res[0];
            $nextRow = is_null($res[1]['id']) ? null : $res[1];
        }

        return [$prevRow, $nextRow];
    }
}
