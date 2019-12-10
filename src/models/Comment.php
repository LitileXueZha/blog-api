<?php

/**
 * 评论 - 数据模型
 */

namespace TC\Model;

require_once __DIR__.'/DB.php';

use DBStatement;

class Comment
{
    /**
     * 评论表名
     * 
     * @var String
     */
    const NAME = 'comment';

    /**
     * 查询格式
     * 
     * @var String
     */
    const FORMAT = 'comment_id as id, parent_id, name, content,
                    type, label, create_at';
    
    /**
     * 添加一条评论
     * 
     * @param Array 评论数据
     * @return Array 插入成功后的记录
     */
    public static function add($data)
    {
        $db = DB::init();
        $tb = self::NAME;

        // 唯一 id 生成
        $data['comment_id'] = DB::shortId();

        $columns = array_keys($data);
        $dbs = new DBStatement($tb);

        // dbs 操作
        $dbs->insert($columns);

        $statement = $dbs->toString();
        $sql = $db->prepare($statement);

        foreach ($data as $key => $value) {
            $sql->bindValue(":$key", $value);
        }

        $sql->execute();

        $res = self::get(['comment_id' => $data['comment_id']]);

        return $res['items'][0];
    }

    /**
     * 获取评论
     * 
     * @param Array 查询条件
     * @param Array 额外参数。例如 LIMIT、ORDER BY
     * @return Array 留言数据。格式为 [ 'total', 'items' ]
     */
    public static function get($params, $options = [])
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
        $dbs->select('comment_id as id, parent_id, name, content, type, label, create_at')
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
     * 更新评论
     * 
     * @param String 评论 id
     * @param Array 要更新的评论数据
     * @return Array 更新后的记录。为空则表示数据不存在
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
            ->where(['__WHERE__' => 'comment_id=:id AND _d=0']);

        $statement = $dbs->toString();
        $sql = $db->prepare($statement);

        foreach ($data as $key => $value) {
            $sql->bindValue(":$key", $value);
        }
        // 防 sql 注入
        $sql->bindValue(':id', $id);

        $sql->execute();

        // 更新后查询，如果为空则表示数据不存在
        $res = self::get(['comment_id' => $id, '_d' => 0]);

        return $res['items'];
    }

    /**
     * 删除评论
     * 
     * @param String 评论 id
     * @return Number 受影响的行数。为 0 则表示数据不存在
     */
    public static function delete($id)
    {
        $db = DB::init();
        $tb = self::NAME;
        // 防 sql 注入，转义之
        $id = $db->quote($id);

        $statement = "UPDATE $tb SET _d=1 WHERE comment_id=$id";

        // 执行获取受影响的行数
        $count = $db->exec($statement);

        return $count;
    }
}
