<?php

/**
 * 日志记录
 * 
 * @example [2019-04-20 11:20:31] 统一捕获.ERROR: Undefined variable: req. #0 /home/admin/blog-api/index.php(99)
 */

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Log
{
    // 文件地址
    private static $files = [
        'ERROR' => DIR_ROOT .'/logs/error.log',
    ];

    /**
     * 输出到日志文件
     * 
     * @example 示例入参
     * [
     *  'type' => '定义的日志文件类型',
     *  'channel' => '日志名',
     *  'msg' => '日志消息',
     *  'file' => '所在文件',
     *  'line' => '所在行数',
     *  'trace_str' => '生成的堆栈追踪字符串'
     * ]
     * 
     * @param Array $log 日志元数据
     */
    private static function exec($log)
    {
        $type = $log['type'];
        $channel = $log['channel'];
        $msg = $log['msg'];
        $file = $log['file'];
        $line = $log['line'];
        $traceStr = $log['trace_str'];

        $logger = new Logger("$channel.$type");
        $handler = new StreamHandler(self::$files[$type]);
        $formatter = new LineFormatter("[%datetime%] %channel%: %message%\n", "Y-m-d H:i:s", true);
        // 加个 tab 缩进优化展示
        $formatTrace = "\n\t". str_replace("\n", "\n\t", $traceStr);

        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        $logger->info($msg.$formatTrace);
    }

    /**
     * 错误记录
     * 
     * @param String $channel 记录名
     * @param String $msg 错误消息
     * @param String $file 文件名
     * @param Number $line 行数
     * @param String|NULL $traceStr 由 Exception 类型生成的堆栈字符串
     */
    public static function error($channel, $msg, $file, $line, $traceStr = '')
    {
        self::exec([
            'type' => 'ERROR',
            'channel' => $channel,
            'msg' => $msg,
            'file' => $file,
            'line' => $line,
            'trace_str' => $traceStr,
        ]);
    }
    
    /**
     * 输出数据到浏览器
     * 
     * @param mixed $data
     */
    public static function debug($data)
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        echo json_encode([
            'DEBUG' => true,
            'description' => '调试数据输出',
            'data' => $data,
        ]);
        exit();
    }
}
