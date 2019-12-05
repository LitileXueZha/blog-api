<?php

/**
 * SQL 语句工具
 * 
 * 使用：
 * ```php
 * new DBStatement('table name', 'joined table')
 *     ->select(['id', 'create_at as createAt', '__JOIN__' => ['name', 'name as n']])
 *     ->on('tag', 'tag_name')
 *     ->on('a', 'b')
 *     ->where(['name', 'age' => 5, '__WHERE__' => ['a', 'b']])
 *     ->limit('2, 10')
 *     ->orderBy('create_at DESC')
 *     ->end();
 * ```
 */

class DBStatement
{
    // SQL 语句类型
    const SELECT = 1;
    const UPDATE = 2;
    const INSERT = 3;
    const DELETE = 4;

    /**
     * 表名称
     * 
     * @var String
     */
    private $tb;

    /**
     * 关联查询的表名
     * 
     * @var String
     */
    private $tbJoin;

    /**
     * SQL 语句类型
     * 
     * @var Number
     */
    private $type;

    /**
     * 当前 SQL 语句实例配置。目前支持的条件：
     * + `WHERE`
     * + `LIMIT`
     * + `ORDER BY`
     * + `LEFT JOIN`
     * + `ON`
     * 
     * @var Array
     */
    private $opts = [
        'LIMIT' => 'LIMIT 0, 10', // 默认分页 10 条
        'ORDER BY' => 'ORDER BY create_at DESC',  // 默认创建时间倒序
    ];

    function __construct($tbname, $tbJoin = NULL)
    {
        $this->tb = $tbname;
        $this->tbJoin = $tbJoin;
    }

    /**
     * SQL 语句类型之 `SELECT`
     * 
     * 格式化要查询的字段。例如 ['name', 'name as n'] => "name,name as n"
     * 
     * @param Array
     */
    public function select($keys)
    {
        $tb = $this->tb;
        $tbJoin = $this->tbJoin;
        $keysArr = $keys;

        // 有连接表时特殊处理
        if (isset($tbJoin)) {
            $keysArr = [];

            foreach ($keys as $index => $key) {
                if ($index === '__JOIN__') {
                    // 指明获取连接表的字段
                    foreach ($key as $keyJoin) {
                        $keysArr[] = "$tbJoin.$keyJoin";
                    }
                    continue;
                }

                $keysArr[] = "$tb.$key";
            }
        }

        $keyStr = implode(',', $keysArr);

        // 挂载到 opts 上
        $this->opts['FORMAT'] = $keyStr;
        // 设置当前语句实例类型
        $this->type = self::SELECT;

        return $this;
    }

    /**
     * SQL 语句类型之 `DELETE`
     */
    public function delete()
    {
        // TODO:
    }

    /**
     * 生成 SQL 语句
     * 
     * @return String SQL 语句
     */
    public function end()
    {
        $statement = '';

        switch ($this->type) {
            case self::SELECT: {
                $tb = $this->tb;
                $tbJoin = $this->tbJoin;
                $format = $this->opts['FORMAT'];
                $where = $this->opts['WHERE'];
                $orderBy = $this->opts['ORDER BY'];
                $limit = $this->opts['LIMIT'];

                if (isset($tbJoin)) {
                    $on = $this->opts['ON'];
                    $statement = "SELECT SQL_CALC_FOUND_ROWS FROM $tb
                                LEFT JOIN $tbJoin ON $on
                                WHERE $where ORDER BY $tb.$orderBy LIMIT $limit";
                } else {
                    $statement = "SELECT SQL_CALC_FOUND_ROWS FROM $tb
                                WHERE $where ORDER BY $tb.$orderBy LIMIT $limit";
                }

                return $statement;
            }
            case self::INSERT:
            break;
            case self::UPDATE:
            break;
            case self::DELETE:
                return;
            default:
                return;
        }
    }

    /**
     * 条件 `WHERE`。目前支持的方式：
     * + `AND` 逻辑与
     * + `IN` 多数据之一 enum
     * 
     * 例子：`['id', 'name as n', 'age' => 5]`
     * 
     * 当出现键值对而不是普通的字符串时，表明使用 IN 方式，
     * 将会转化为 `$key IN (:$key$index, :$key$index)`，
     * 指定第二个参数可改变转化方式。下面是个参考
     * 
     * ```php
     * $->where(['age' => 5], function ($key, $index) {
     *     return ":$key$index";
     * });
     * ```
     * 
     * @param Array 要查询的列名。例如：['id', 'name as n', 'age' => 5]
     * @param Function 转化 in 查询的方法。默认转为 `$key$index`
     */
    public function where($keys, $in = NULL)
    {
        $this->opts['WHERE'] = $keys;
        $this->opts['IN'] = $in;

        return $this;
    }

    public function limit($limit)
    {
        $this->opts['LIMIT'] = "LIMIT $limit";

        return $this;
    }

    public function orderBy($orderBy)
    {
        $this->opts['ORDER BY'] = "ORDER BY $orderBy";

        return $this;
    }

    public function join($tbname)
    {
        $this->opts['LEFT JOIN'] = $tbname;

        return $this;
    }

    public function on($keys)
    {
        $this->opts['ON'] = $keys;
    }
}
