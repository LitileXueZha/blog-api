<?php

// 模型需要手动引用
// FIXME: 找下 phpunit 自动引入方式
require_once DIR_ROOT.'/src/models/AccessControl.php';

use \TC\Model\AccessControl as ACL;
use \PHPUnit\Framework\TestCase;

/**
 * @testdox 测试数据模型_AccessControl_访问控制
 */
class AccessControlTest extends TestCase
{
    protected function setUp()
    {
        // 测试用的 acl 权限列表
        $file = __DIR__.'/AccessControl.json';

        ACL::init($file);
    }

    /**
     * @testdox 是否权限查询正确
     */
    public function testGetacl()
    {
        $api = 'GET /404';
        $res = ACL::getacl($api, 'tao');

        $this->assertSame(NULL, $res);

        $api = 'GET /v1/user';
        $res = ACL::getacl($api, 'foo');

        $this->assertSame(1, $res);

        $api = 'POST /v1/user/foo';
        $res = ACL::getacl($api, 'tao');

        $this->assertSame(0, $res);

        $api = 'PUT /v1/user';
        $res = [];
        $res[] = ACL::getacl($api, 'tao');
        $res[] = ACL::getacl($api, 'foo');
        $res[] = ACL::getacl($api, 'foo', 'say');
        $res[] = ACL::getacl($api, 'bar', 'say');

        $this->assertEquals([1, 0, 0, 1], $res);
    }
}
