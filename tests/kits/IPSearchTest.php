<?php

/**
 * @testdox 测试工具类_IPSearch_IP地理位置查询
 */
class IPSearchTest extends \PHPUnit\Framework\TestCase
{
    const HOST = '本地主机';
    const PRIVATE_NETWORK = '专用网络';
    const SOFTWARE = '本地未知网络';

    /**
     * @testdox 是否本地IP正确返回
     */
    public function testLocalIp()
    {
        $tests = [
            '127.0.0.1' => self::HOST,
            '10.0.0.0' => self::PRIVATE_NETWORK,
            '172.16.0.1' => self::PRIVATE_NETWORK,
            '192.168.0.1' => self::PRIVATE_NETWORK,
            '0.0.0.0' => self::SOFTWARE,
        ];

        foreach ($tests as $ip => $addr) {
            $this->assertEquals($addr, IPSearch::ip138($ip));
        }
        $this->assertEquals(self::PRIVATE_NETWORK, IPSearch::chinaz('172.31.255.255'));
    }

    /**
     * @testdox 是否服务正常可用
     */
    public function testService()
    {
        $this->assertNotEmpty(IPSearch::ip138('127.0.0.1'));
        $this->assertNotEmpty(IPSearch::chinaz('127.0.0.1'));
    }

    /**
     * @depends testService
     * @testdox 是否`ip138`查询IP地址正确
     */
    public function testIp138()
    {
        // 数据来源：https://www.ip138.com/iplookup.asp?ip=47.52.166.204&action=2
        $ip = '47.52.166.204';
        $addr = "香港特别行政区  阿里云 数据中心";

        $this->assertEquals($addr, IPSearch::ip138($ip));
    }

    /**
     * @depends testService
     * @testdox 是否`chinaz`查询IP地址正确
     */
    public function testChinaz()
    {
        // 数据来源：http://ip.tool.chinaz.com/47.52.166.204
        $ip = '47.52.166.204';
        $addr = "香港 阿里云";

        $this->assertEquals($addr, IPSearch::chinaz($ip));
    }
}
