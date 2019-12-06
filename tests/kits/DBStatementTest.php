<?php

/**
 * @testdox 测试工具类_DBStatement_SQL语句构造器
 */
class DBStatementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testdox 是否`SELECT`语句能正确生成
     */
    public function testSelect()
    {
        // 1. 简单的一个查询
        $dbs = new DBStatement('article');

        $dbs->select('article_id as id', 'title', 'content', 'create_at')
            ->where(['__WHERE__' => '_d=0'])
            ->limit('2,10');
        
        $statement = $dbs->toString();
        $exp1 = "SELECT SQL_CALC_FOUND_ROWS article_id as id,title,content,create_at FROM article";
        $join = '';
        $orderBy = '';
        $exp2 = "WHERE _d=0 $orderBy LIMIT 2,10";
        $expect = "$exp1 $join $exp2";
        
        $this->assertEquals($expect, $statement);

        // 2. 更复杂的连接表
        $tb = 'article';
        $tbJoin = 'tag';
        $dbs = new DBStatement($tb, $tbJoin);
        
        $dbs->select(
                "$tb.article_id as id",
                "$tb.title",
                "$tbJoin.display_name as tag_name"
            )
            ->on("$tb.tag", "$tbJoin.name")
            ->on('a', 'b')
            ->where(["$tb.id", "$tb.type" => 2, '__WHERE__' => "$tb._d>0"])
            ->orderBy('modify_at DESC')
            ->limit('0,12');
        
        $statement = $dbs->toString();
        $exp1 = "SELECT SQL_CALC_FOUND_ROWS $tb.article_id as id,$tb.title,$tbJoin.display_name as tag_name FROM $tb";
        $join = "LEFT JOIN $tbJoin ON $tb.tag=$tbJoin.name,a=b";
        $exp2 = "WHERE $tb.id=:id AND $tb.type IN (:type0,:type1) AND $tb._d>0";
        $exp3 = "ORDER BY $tb.modify_at DESC LIMIT 0,12";
        $expect = "$exp1 $join $exp2 $exp3";

        $this->assertEquals($expect, $statement);
    }

    /**
     * @testdox 是否`INSERT`语句能正确生成
     */
    public function testInsert()
    {
        $dbs = new DBStatement('person');

        $dbs->insert(['id', 'name']);

        $statement = $dbs->toString();
        $expect = 'INSERT INTO person (id,name) VALUES (:id,:name)';

        $this->assertEquals($expect, $statement);
    }

    /**
     * @testdox 是否`UPDATE`语句能正确生成
     */
    public function testUpdate()
    {
        $dbs = new DBStatement('person');

        $dbs->update(['name', 'age'])
            ->where(['__WHERE__' => 'foo_id=:id AND _d=0']);
        
        $statement = $dbs->toString();
        $expect = 'UPDATE person SET name=:name,age=:age WHERE foo_id=:id AND _d=0';

        $this->assertEquals($expect, $statement);

        // 全部更新测试
        $dbs = new DBStatement('person');

        $dbs->update(['id', 'age']);

        $statement = $dbs->toString();
        $where = '';
        $expect = "UPDATE person SET id=:id,age=:age $where";

        $this->assertEquals($expect, $statement);
    }

    /**
     * @testdox 是否语句类型方法未调用返回空语句
     */
    public function testEmpty()
    {
        $dbs = new DBStatement('person');

        $dbs->on('a', 'b')
            ->where(['person.id as personId', 'sex' => 2]);

        $statement = $dbs->toString();

        $this->assertEquals(NULL, $statement);
    }
}
