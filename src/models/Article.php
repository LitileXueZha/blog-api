<?php

/**
 * 文章 - 数据模型
 */

namespace TC\Model;

require_once __DIR__.'/DB.php';

class Article
{
    /**
     * 数据库表名称
     * 
     * @var String
     */
    const NAME = 'article';

    /**
     * 查询返回格式
     * 
     * @var String
     */
    const FORMAT = 'article_id as id, title, summary, content, tag,
                    status, category, bg, create_at';

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
        [$col, $placeholder] = DB::getPlaceholderByKeys($columns);
        
        $statement = "INSERT INTO $tb ($col) VALUES ($placeholder)";

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
        $join = "LEFT JOIN $tbJoin ON $tb.tag=$tbJoin.name";
        // 查询格式
        $format = "$tb.article_id as id, $tb.title, $tb.summary, $tb.content,
                    $tb.tag, $tbJoin.display_name as tag_name, $tb.status, $tb.category,
                    $tb.bg, $tb.create_at";
        // 分页
        [
            'limit' => $limit,
            'orderBy' => $orderBy,
        ] = DB::getOptsOrDefault($options);

        $columns = array_keys($params);
        $placeholder  = implode(' AND ', array_map(function ($key) use ($tb, $params) {
            $param = $params[$key];

            if (is_array($param)) {
                // 复杂的 sql 数组型参数查询
                // 吐血的 PDO 不支持直接 IN (1,2,3) 形式，只能一个个绑定
                // @link https://stackoverflow.com/questions/14767530/php-using-pdo-with-in-clause-array
                $str = [];

                foreach ($param as $i => $value) {
                    $str[] = ":$key$i";
                }
                $str = implode(',', $str);

                return "$tb.$key IN ($str)";
            }

            return "$tb.$key = :$key";
        }, $columns));

        $statement = "SELECT SQL_CALC_FOUND_ROWS $format FROM $tb $join
                    WHERE $placeholder ORDER BY $tb.$orderBy LIMIT $limit";

        $sql = $db->prepare($statement);

        foreach ($columns as $key) {
            $param = $params[$key];

            if (is_array($param)) {
                // 复杂的 sql 数组型参数查询
                foreach ($param as $i => $val) {
                    $sql->bindValue(":$key$i", $val);
                }
                
                continue;
            }

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
        $placeholder = implode(',', array_map(function ($key) {
            return "$key=:$key";
        }, $columns));

        // 筛选未逻辑删除
        $statement = "UPDATE $tb SET $placeholder WHERE article_id=:id AND _d=0";

        $sql = $db->prepare($statement);

        foreach ($columns as $key) {
            $sql->bindValue(":$key", $data[$key]);
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
        // 查询格式。只需要查询文本搜索的几个字段，并添加一列 type 固定值为 article
        $format = "article_id as id, title, summary, text_content, 'article' as type, create_at";
        // 分页
        $limit = empty($options['limit']) ? '0, 10' : $options['limit'];
        // 防 sql 注入，转义之
        $q = $db->quote($q);

        // 默认 IN NATURAL LANGUAGE MODE
        $statement = "SELECT SQL_CALC_FOUND_ROWS $format FROM $tb WHERE _d=0 AND
                        match(title, summary, text_content) against($q) LIMIT $limit";

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
        // 查询格式
        $format = "article_id as id, title, summary, content, modify_at";
        // 分页
        [
            'limit' => $limit,
            'orderBy' => $orderBy,
        ] = DB::getOptsOrDefault($options);

        $columns = array_keys($params);
        $placeholder  = implode(' AND ', array_map(function ($key) {
            return "$key = :$key";
        }, $columns));

        $statement = "SELECT SQL_CALC_FOUND_ROWS $format FROM $tb
                    WHERE $placeholder ORDER BY $orderBy LIMIT $limit";

        $sql = $db->prepare($statement);

        foreach ($columns as $key) {
            $sql->bindValue(":$key", $params[$key]);
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
}
