<?php

/**
 * 错误处理
 * 
 * 在整个应用启动时 ErrorHandler::init()
 */

class ErrorHandler
{
    // 错误码定义
    const ERROR = 100;
    const EXCEPTION = 200;

    protected static $types = [
        self::ERROR => 'ERROR',
        self::EXCEPTION => 'EXCEPTION',
    ];

    public static function init()
    {
        error_reporting(E_ALL | E_STRICT);

        // 设置全局捕获 Error 与 Exception
        self::handleError();
        self::handleException();
    }

    public static function handleError()
    {
        /**
         * 全局错误捕获。文档：https://www.php.net/manual/zh/function.set-error-handler.php
         * 
         * @param Number $errno 错误级别码
         * @param String $errstr 错误信息
         * @param String $errfile 文件名
         * @param Number $errline 行数
         */
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            $err = [
                'code' => $errno,
                'msg' => $errstr,
                'file' => $errfile,
                'line' => $errline,
            ];

            // 保证错误格式一致。额外的操作
            $err['trace'] = debug_backtrace();
            $err['trace_str'] = "at $errfile($errline)";

            self::handle($err, self::ERROR);
        });
    }

    public static function handleException()
    {
        /**
         * 全局异常捕获。文档：https://www.php.net/manual/zh/function.set-exception-handler.php
         * 
         * @static Exception::getMessage 异常信息
         * @static Exception::getCode 错误级别码
         * @static Exception::getFile 文件名
         * @static Exception::getLine 行数
         * @static Exception::getTrace 异常堆栈，类似 console.trace
         * @static Exception::getTraceAsString 异常堆栈字符串，每一条信息记录调用详情
         */
        set_exception_handler(function ($e) {
            $file = $e->getFile();
            $line = $e->getLine();
            $traceStr = $e->getTraceAsString();
            // 部分获取的 trace 中少了错误本身。手动加上
            $errSelf = "at $file($line)\n";
            $err = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'file' => $file,
                'line' => $line,
                'trace' => $e->getTrace(),
                'trace_str' => $errSelf.$traceStr,
            ];

            self::handle($err, self::EXCEPTION);
        });
    }

    /**
     * 处理错误信息
     * 
     * @param Array $err 错误数据
     * @param Number $code 错误码。ErrorHandler 中定义的
     * 
     * @return void
     */

    private static function handle($err, $code)
    {
        // 启用调试时，直接将数据输出到浏览器
        if (DEBUG) {
            // 可能错误信息编码是 gbk，转一下。应该只有中文环境存在了
            // $err['msg'] = iconv('gbk', 'utf-8', $err['msg']);
            // 上面的转法正常 utf-8 报错
            // 设置检测顺序，范围要从小到大！！！
            mb_detect_order('ASCII,UTF-8,GBK');
            $encoding = mb_detect_encoding($err['msg']);
            $err['msg'] = mb_convert_encoding($err['msg'], 'utf-8', $encoding);
            Log::debug([
                self::$types[$code] => $err['msg'],
                'file' => $err['file'],
                'line' => $err['line'],
                'trace' => $err['trace'],
                'trace_str' => $err['trace_str'],
            ]);
        }

        // 生产环境记录错误日志
        if (ENV === 'production') {
            // logs
            Log::error('统一捕获', $err['msg'], $err['file'], $err['line'], $err['trace_str']);
        }

        // 默认返回错误
        $res = new Response(HttpCode::INTERNAL_SERVER_ERROR);
        $res->end();
    }
}

