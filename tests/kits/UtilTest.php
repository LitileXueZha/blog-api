<?php

/**
 * @testdox 测试工具类_Util_通用函数
 */
class UtilTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testdox 是否函数能合成
     */
    public function testCompose()
    {
        $this->assertIsCallable(Util::compose([]));
        
        $func = function ($next) {
            return function () use ($next) {
                $next();
            };
        };

        $this->assertIsCallable(Util::compose([$func]));
    }

    /**
     * @testdox 是否函数合成后功能正确
     */
    public function testComposeFunc()
    {
        $str = '';
        $a = function ($next) use (&$str) {
            return function () use ($next, &$str) {
                $str .= 'a';
                $next();
                $str .= 'a';
            };
        };
        $b = function ($next) use (&$str) {
            return function () use ($next, &$str) {
                $str .= 'b';
                $next();
                $str .= 'b';
            };
        };
        $compose = Util::compose([$a, $b]);

        $compose();
        $this->assertEquals('abba', $str);
    }

    /**
     * @testdox 是否数据校验正确
     */
    public function testValidate()
    {
        $data = ['name' => 'tao'];
        $rules = [
            'name' => ['type' => 'string'],
        ];
        $msg = Util::validate($data, $rules);

        // TODO: 测试用例待做
        $this->assertEquals('', $msg);
    }

    /**
     * @testdox 是否`shortId`能正确生成
     */
    public function testShortId()
    {
        $result = [
            0 => 'a',
            1 => 'b',
            4000 => 'bcG',
            47329479 => 'dmKIR',
        ];
        
        foreach ($result as $num => $id) {
            $this->assertEquals($id, Util::shortId($num));
        }
    }
}
