<?php

/**
 * 标签 - 数据模型
 */

namespace TC\Model;

require_once __DIR__.'/DB.php';

use DBStatement;

class Tag
{
    /**
     * 标签表名
     * 
     * @var String
     */
    const NAME = 'tag';

    /**
     * 查询格式化
     * 
     * @var String
     */
    const FORMAT = 'name as id, display_name as name, click,
                    status, create_at';
    
    /**
     * 添加一个标签
     * 
     * @param Array 标签数据
     * @return Array 插入成功后的记录
     */
    public static function add($data)
    {
        $db = DB::init();
        $tb = self::NAME;

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

        // 执行语句
        $sql->execute();

        $res = self::get(['name' => $data['name'], '_d' => 0]);

        return $res['items'][0];
    }

    /**
     * 查询标签
     * 
     * @param Array 查询条件
     * @param Array 查询额外参数。例如 ORDER BY
     * @return Array 标签数据。格式为 [ total, items ]
     */
    public static function get($params, $options = [])
    {
        $db = DB::init();
        $tb = self::NAME;

        $orderBy = DB::getOptsOrDefault($options)['orderBy'];
        $columns = array_keys($params);
        $dbs = new DBStatement($tb);

        // dbs 查询
        $dbs->select('name as id, display_name as name, click, status, create_at')
            ->where($columns)
            ->orderBy($orderBy);

        $statement = $dbs->toString();
        $sql = $db->prepare($statement);

        foreach ($columns as $key) {
            $sql->bindValue(":$key", $params[$key]);
        }

        $sql->execute();

        $res = $sql->fetchAll();
        $sqlCount = $db->query('SELECT FOUND_ROWS()');
        $count = $sqlCount->fetch();

        return [
            'total' => $count['FOUND_ROWS()'],
            'items' => $res,
        ];
    }

    /**
     * 更新标签
     * 
     * @param String 标签 id
     * @param Array 要更新的数据
     * @return Array 更新后的标签记录。为空数组则表示不存在
     */
    public static function set($id, $data)
    {
        $db = DB::init();
        $tb = self::NAME;

        $columns = array_keys($data);
        $placeholder = implode(',', array_map(function ($key) {
            return "$key = :$key";
        }, $columns));

        $statement = "UPDATE $tb SET $placeholder WHERE `name`=:id AND _d=0";

        $sql = $db->prepare($statement);

        foreach ($data as $key => $value) {
            $sql->bindValue(":$key", $value);
        }
        // 防止 id sql 注入
        $sql->bindValue(':id', $id);

        $sql->execute();

        // 更新完毕后查询，如果为空，表示此条数据不存在
        $res = self::get(['name' => $id, '_d' => 0]);

        return $res['items'];
    }

    /**
     * 删除标签
     * 
     * @param String 标签 id
     * @return Number 受影响的行数。可通过此来判断是否存在
     */
    public static function delete($id)
    {
        $db = DB::init();
        $tb = self::NAME;
        // 转义，防 sql 注入
        $id = $db->quote($id);
        $statement = "UPDATE $tb SET _d=1 WHERE `name`=$id";

        $count = $db->exec($statement);

        return $count;
    }
}
