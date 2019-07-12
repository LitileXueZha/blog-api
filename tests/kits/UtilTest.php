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
        // 成功校验
        $data = [
            'name' => 'tao',
            'age' => 24,
            'married' => false,
            'sex' => 'man',
            'pets' => ['cat', 'dog'],
            'email' => '123@great.cn',
            'github' => 'https://github.com/litilexuezha',
            'phone' => '18255447846',
            'whoami' => 'dafas',
        ];
        $rules = [
            'name' => ['type' => 'string'],
            'age' => ['type' => 'number'],
            'married' => ['type' => 'bool'],
            'sex' => ['type' => 'enum', 'enum' => ['man', 'woman']],
            'pets' => ['type' => 'array'],
            'email' => ['type' => 'email'],
            'github' => ['type' => 'url'],
            'phone' => ['type' => 'string', 'pattern' => "/^1[\d]{10}$/"],
            'whoami' => [
                'type' => 'string',
                'validator' => function ($data) {
                    return NULL;
                },
            ],
        ];
        $msg = Util::validate($data, $rules);

        $this->assertEquals(NULL, $msg);

        // 失败校验
        $error = '数据格式不正确'; // 此处保证与 Util::$validateRule['error'] 一样
        $fail = [[
            [],
            ['name' => ['type' => 'string', 'required' => true, 'error' => $error]]
        ], [
            ['name' => 24],
            ['name' => ['type' => 'string']]
        ], [
            ['age' => 'tao'],
            ['age' => ['type' => 'number']]
        ], [
            ['married' => NULL],
            ['married' => ['type' => 'bool']]
        ], [
            ['sex' => 'none'],
            ['sex' => ['type' => 'enum', 'enum' => ['man', 'woman']]]
        ], [
            ['pets' => new StdClass],
            ['pets' => ['type' => 'array']]
        ], [
            ['email' => 'dafdsa.com'],
            ['email' => ['type' => 'email']]
        ], [
            ['github' => 'http:/daf'],
            ['github' => ['type' => 'url']]
        ], [
            ['phone' => '182554427846'],
            ['phone' => ['type' => 'string', 'pattern' => "/^1[\d]{10}$/"]]
        ], [
            ['whoami' => 'daf'],
            ['whoami' => ['type' => 'string', 'validator' => function () use ($error) { return $error; }]]
        ]];
        
        foreach ($fail as $key =>  $item) {
            $res = Util::validate($item[0], $item[1]);
            
            $this->assertEquals($error, $res);
        }
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
