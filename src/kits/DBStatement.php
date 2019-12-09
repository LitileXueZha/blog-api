<?php

/**
 * SQL 语句工具
 * 
 * 目前支持三种类型：
 * + `select()` 查询
 * + `insert()` 插入
 * + `update()` 更新
 * 
 * 相对简单的语句其实可以直接写 SQL
 * 
 * 使用：
 * ```php
 * new DBStatement('table name', 'joined table')
 *     ->select('id', 'create_at as createAt', 'tb1.name as n')
 *     ->on('tb1.tag', 'tb2.tag_name')
 *     ->on('tb1.a', 'tb2.b')
 *     ->where(['name', 'age' => 5, '__WHERE__' => 'tb1.age > 10 OR tb2.name IS NULL'])
 *     ->limit('2, 10')
 *     ->orderBy('create_at DESC')
 *     ->toString();
 * 
 * new DBStatement('table name')
 *     ->insert(['id', 'name'])
 *     ->toString();
 * 
 * new DBStatement('table name)
 *     ->update(['name', 'age'])
 *     ->where(['id', '__WHERE__' => '_d=0'])
 *     ->toString();
 * ```
 */

class DBStatement
{
    // SQL 语句类型
    const SELECT = 1;
    const UPDATE = 2;
    const INSERT = 3;
    // const DELETE = 4; // 暂不提供此破坏性方法

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
        // 解决 undefined index
        'WHERE' => NULL,
        'ORDER BY' => NULL,
        'LIMIT' => NULL
        // 'LIMIT' => 'LIMIT 0, 10', // 默认分页 10 条
        // 'ORDER BY' => 'ORDER BY create_at DESC',  // 默认创建时间倒序
    ];

    function __construct($tbname, $tbJoin = NULL)
    {
        $this->tb = $tbname;
        $this->tbJoin = $tbJoin;
    }

    /**
     * SQL 语句类型之 `SELECT`
     * 
     * 拼接要查询的字段。例如 `select('name', 'tb1.name as n', 'tb2.age')`
     * => "name,tb1.name as n,tb2.age"
     * 
     * @param Array
     */
    public function select(...$keys)
    {
        $keyStr = implode(',', $keys);

        // 挂载到 opts 上
        $this->opts['FORMAT'] = $keyStr;
        // 设置当前语句实例类型
        $this->type = self::SELECT;

        return $this;
    }

    /**
     * SQL 语句类型之 `INSERT`
     * 
     * 快捷转化占位字符。例如 `insert(['a', 'b'])` => 'a,b'、':a,:b'
     * 
     * @param Array 要插入的列名数组
     */
    public function insert($keys)
    {
        // 转化占位符
        $keyStr = implode(',', $keys);
        $bindStr = implode(',', array_map(function ($col) {
            return ":$col";
        }, $keys));

        // 挂载 opts
        $this->opts['INSERT_COL'] = $keyStr;
        $this->opts['INSERT_VALUES'] = $bindStr;
        $this->type = self::INSERT;

        return $this;
    }

    /**
     * SQL 语句类型之 `UPDATE`
     * 
     * 快捷转化占位字符。相对简单的语句其实可以直接写 SQL
     * 
     * @param Array 要更新的列名数组
     */
    public function update($keys)
    {
        // 转化占位符
        $keyStr = implode(',', array_map(function ($col) {
            return "`$col`=:$col";
        }, $keys));

        // 挂载 opts
        $this->opts['UPDATE_COL'] = $keyStr;
        $this->type = self::UPDATE;

        return $this;
    }

    /**
     * 生成 SQL 语句
     * 
     * @return String SQL 语句
     */
    public function toString()
    {
        $statement = '';
        $tb = $this->tb;
        $opts = $this->opts;

        switch ($this->type) {
            case self::SELECT: {
                $tbJoin = $this->tbJoin;
                $format = $opts['FORMAT'];
                $where = $opts['WHERE'];
                $orderBy = $opts['ORDER BY'];
                $limit = $opts['LIMIT'];
                $join = '';

                // 存在连接表时，设置之
                if ($tbJoin) {
                    $on = $opts['ON'];
                    $join = "LEFT JOIN $tbJoin $on";
                }
                
                $statement = "SELECT SQL_CALC_FOUND_ROWS $format FROM $tb $join $where $orderBy $limit";

                break;
            }
            case self::INSERT:
                $col = $opts['INSERT_COL'];
                $values = $opts['INSERT_VALUES'];
                $statement = "INSERT INTO $tb ($col) VALUES ($values)";

                break;
            case self::UPDATE:
                $col = $opts['UPDATE_COL'];
                $where = $opts['WHERE'];
                $statement = "UPDATE $tb SET $col $where";

                break;
            default:
                return;
        }

        return $statement;
    }

    /**
     * 条件 `WHERE`。目前支持的方式：
     * + `AND` 逻辑与
     * + `IN` 多数据之一 enum
     * + `__WHERE__` 自定义复杂的查询，将直接拼接不作处理
     * 
     * 例子：`['id', 'age' => 5, '__WHERE__' => 'tb.sex IS NULL']`
     * 
     * 当出现键值对而不是普通的字符串时，表明使用 IN 方式，
     * 将会转化为 `tb.$key IN (:$key$index, :$key$index)`，
     * 指定第二个参数可改变转化方式。下面是个参考
     * 
     * ```php
     * $->where(['age' => 5], function ($key, $index) {
     *     return ":$key__$index";
     * });
     * ```
     * 
     * @param Array 要查询的条件列名。例如：['id', 'age' => 5]
     * @param Function 转化 in 查询的方法。默认转为 `$key$index`
     */
    public function where($keys, $func = NULL)
    {
        $keyArr = [];
        $reg = '/^(\w+\.)/'; // 匹配表前缀

        foreach ($keys as $index => $key) {
            // 1. 单字段转化
            if (is_int($index)) {
                // 删除表前缀
                $column = preg_replace($reg, '', $key);
                $keyArr[] = "$key=:$column";
                continue;
            }

            // 2. 自定义复杂表条件，直接拼接
            if ($index === '__WHERE__') {
                $keyArr[] = $key;
                continue;
            }

            // 3. 多数据之一查询
            // 吐血的 PDO 不支持直接 IN (1,2,3) 形式，只能一个个绑定
            // @link https://stackoverflow.com/questions/14767530/php-using-pdo-with-in-clause-array
            $column = preg_replace($reg, '', $index);
            $range = range(0, $key - 1);
            $inArr = [];
            
            foreach ($range as $i) {
                $inArr[] = $func ? $func($column, $i) : ":$column$i";
            }
            
            $inStr = implode(',', $inArr);
            $keyArr[] = "$index IN ($inStr)";
        }

        $keys = array_map(function () {}, $keys);
        $keyStr = implode(' AND ', $keyArr);

        $this->opts['WHERE'] = "WHERE $keyStr";

        return $this;
    }

    /**
     * 分页设置
     * 
     * 默认 `0,10`
     * 
     * @param String 分页字符串
     */
    public function limit($limit)
    {
        $this->opts['LIMIT'] = "LIMIT $limit";

        return $this;
    }

    /**
     * 排序设置
     * 
     * 默认 `create_at DESC`。有连接表时，以主表为准
     * 
     * @param String 排序字段
     */
    public function orderBy($orderBy)
    {
        $tb = $this->tb;
        $tbJoin = $this->tbJoin;

        // 存在连接表时，以主表字段排序
        if ($tbJoin) {
            $orderBy = "$tb.$orderBy";
        }

        $this->opts['ORDER BY'] = "ORDER BY $orderBy";

        return $this;
    }

    /**
     * 指定连接表字段
     * 
     * 转化示例：`on('tb1.tag', 'tb2.tag_name')` 将会 "ON tb1.tag=tb2.tag_name"
     * 
     * @param String 字段1
     * @param String 字段2
     */
    public function on($column1, $column2)
    {
        $on = empty($this->opts['ON']);

        if ($on) {
            $on = "ON $column1=$column2";
        } else {
            // 持续叠加
            $on = $this->opts['ON'];
            $on .= ",$column1=$column2";
        }

        $this->opts['ON'] = $on;

        return $this;
    }
}
