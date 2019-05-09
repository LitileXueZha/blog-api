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

            self::exec($err, self::ERROR);
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
            $err = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
                'trace_str' => $e->getTraceAsString(),
            ];

            self::exec($err, self::EXCEPTION);
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

    public static function exec($err, $code)
    {
        // 启用调试时，直接将数据输出到浏览器
        if (DEBUG) {
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

