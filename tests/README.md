# PHPUnit

使用的单元测试框架为 [PHPUnit](https://phpunit.readthedocs.io/zh_CN/latest/)，英文版更加全面。

## 开始

首先安装 `phpunit`，阅读入门文档。

理解下单元测试的几个点：

+ **assert** 断言。是否通过的判断工具
+ **coverage** 覆盖率。是否完整地测试

## 基本配置

创建 `tests` 目录。

创建测试文件：

```php
// tests/LogTest.php
// 后缀必须为 *Test.php，否则不会执行
<?php

/**
 * @testdox 测试日志
 */

class LogTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testdox 是否能正常实例化
     */
    public function testConstruct()
    {
        // 此处断言判断测试是否通过
        $this->assertCount(1, []);
    }
}
```

创建 PHPUnit 配置文件 `phpunit.xml.dist`：

```xml
<phpunit colors="true">
    <testsuites>
        <!-- 声明你的测试文件所在 -->
        <testsuite name="单元测试">
            <directory>tests/</directory>
            <file>tests/LogTest.php</file>
        </testsuite>
    </testsuites>

    <filter>
        <!-- 声明源文件覆盖报告 -->
        <whitelist>
            <directory suffix=".php">src/tools/</directory>
            <file>src/app.php</file>
        </whitelist>
    </filter>
</phpunit>
```

创建 composer 命令：

```json
{
    "scripts": {
        "test": "phpunit --testdox --coverage-text"
    }
}
```

或者 `vendor/bin/phpunit --testdox --coverage-text`，没全局安装的话。

## 更高级的

未完待续。。。
