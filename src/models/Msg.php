<?php

/**
 * 留言 - 标签模型
 */

namespace TC\Model;

require_once __DIR__.'/DB.php';

use DBStatement;

class Msg
{
    /**
     * 数据库表名
     * 
     * @var String
     */
    const NAME = 'msg';

    /**
     * 创建留言
     * 
     * @param Array 留言数据
     * @param Array 插入成功后的记录
     */
    public static function add($data)
    {
        $db = DB::init();
        $tb = self::NAME;

        // 唯一 id 生成
        $data['msg_id'] = DB::shortId();

        // 获取 sql 语句占位符
        $columns = array_keys($data);
        $dbs = new DBStatement($tb);

        // dbs 操作
        $dbs->insert($columns);

        $statement = $dbs->toString();
        $sql = $db->prepare($statement);

        // 绑定数据。烦
        foreach ($data as $key => $value) {
            $sql->bindValue(":$key", $value);
        }

        // 插入
        $sql->execute();

        $res = self::get(['msg_id' => $data['msg_id']]);

        return $res['items'][0];
    }

    /**
     * 获取留言
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
        $dbs->select(
                'msg_id as id, name, content, avatar',
                '`read`, site, create_at, create_by'
            )
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

        // 转化 read 字段为 boolean
        foreach ($res as &$item) {
            $item['read'] = (bool) $item['read'];
        }

        return [
            'total' => $count['FOUND_ROWS()'],
            'items' => $res,
        ];
    }

    /**
     * 更新留言
     * 
     * @param String 留言 id
     * @param Array 要更新的留言数据
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
            ->where(['__WHERE__' => 'msg_id=:id AND _d=0']);

        $statement = $dbs->toString();
        $sql = $db->prepare($statement);

        // 绑定数据
        foreach ($data as $key => $value) {
            $sql->bindValue(":$key", $value);
        }
        // 防 sql 注入
        $sql->bindValue(':id', $id);

        $sql->execute();

        // 更新后查询，如果为空则数据不存在
        // NOTE: 如果是被逻辑删除的数据，还是会被更新。应该在逻辑层做控制
        $res = self::get(['msg_id' => $id, '_d' => 0]);

        return $res['items'];
    }

    /**
     * 删除留言
     * 
     * @param String 留言 id
     * @return Number 受影响的行数。如果为 0 则表示数据不存在
     */
    public static function delete($id)
    {
        $db = DB::init();
        $tb = self::NAME;
        // 防 sql 注入，转义之
        $id = $db->quote($id);

        $statement = "UPDATE $tb SET _d=1 WHERE msg_id=$id";

        // 执行获取受影响行数
        $count = $db->exec($statement);

        return $count;
    }
}
