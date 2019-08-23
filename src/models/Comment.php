<?php

/**
 * 评论 - 数据模型
 */

namespace TC\Model;

require_once __DIR__.'/DB.php';

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
                    type, create_at';

    /**
     * 获取留言
     * 
     * @param Array 查询条件
     * @param Array 额外参数。例如 LIMIT、ORDER BY
     * @return Array 留言数据。格式为 [ 'total', 'items' ]
     */
    public static function get($params, $options)
    {
        $db = DB::init();
        $tb = self::NAME;
    }
}
