<?php

/**
 * @testdox 测试工具类_Response_返回给前端的逻辑
 */
class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testdox 是否能正确实例化
     */
    public function testConstructor()
    {
        $res = new Response(200);

        $this->assertInstanceOf(Response::class, $res);
    }
}
