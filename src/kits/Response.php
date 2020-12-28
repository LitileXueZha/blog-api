<?php

/**
 * 返回数据格式
 *
 * @example { "code": 1, "data": [1, 2] }
 * @example { "code": -1, "error": "请求失败" }
 */

class Response
{
    // 返回给前端的 code 标识
    const CODE_SUCCESS = 1; // 响应成功
    const CODE_FAIL = 0; // 响应失败，一般是客户端错误
    const CODE_ERROR = -1; // 响应失败，服务端错误

    // HTTP 状态码
    private $httpCode;
    // HTTP 头部。默认选项
    private $headers = [
        'Content-Type: application/json',
        // 跨域
        'Access-Control-Allow-Origin: '. (DEBUG ? '*' : CORS),
        'Access-Control-Allow-Methods: OPTIONS,HEAD,GET,POST,PUT,DELETE',
        'Access-Control-Allow-Headers: Authorization,Content-Type',
    ];

    // 返回 body 数据
    private $code;
    private $error = '等等，服务器出bug了!';
    private $data;


    /**
     * 初始化
     * 
     * @param Number $code HTTP 状态码
     * @param Array|Object $data 请求成功的数据（简写方式，也可通过 appendData 添加）
     */
    public function __construct($code, $data = [])
    {
        $this->httpCode = $code;

        // 定义返回 code
        if ($code >= 500) $this->code = self::CODE_ERROR;
        elseif ($code >= 400) $this->code = self::CODE_FAIL;
        else $this->code = self::CODE_SUCCESS;

        // 设置数据
        $this->data = $data;
    }

    /**
     * 重置给前端的 code 标识。正常情况下不应该使用
     * 
     * @param Number $resCode
     */
    public function resetResCode($resCode)
    {
        $this->code = $resCode;
    }

    /**
     * 添加 HTTP header
     * 
     * 只在返回时设置，先赋值给 Response 实例对象
     * 可传的类型有 3 种：
     * 1. 与原生 header 相同入参。Exp: 'Content-Type: application/json'
     * 2. 纯数组项，每项参数与 1 相同。Exp: ['Content-Type: application/json', 'Cache-Control: no-cache']
     * 3. 对象型数组。Exp: ['Content-Type' => 'application/json']
     * 
     * @param String|Array $header
     * 
     * @return void
     */
    public function addHeader($header)
    {
        // 字符串类型
        if (is_string($header)) {
            $this->headers[] = $header;
            return;
        }

        // 数组类型
        if (is_array($header)) {
            foreach($header as $key => $value) {
                if (is_int($key)) {
                    // 纯数组项
                    $this->headers[] = $header;
                    continue;
                }

                // 对象型
                $this->headers[] = "$key: $value";
            }
        }
    }

    /**
     * 定义给前端的 error 提示
     * 
     * @param String $msg
     */
    public function setErrorMsg($msg)
    {
        $this->error = $msg;
    }

    /**
     * 添加返回数据
     * 
     * @param Array $data
     */
    public function appendData(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (is_int($key)) $this->data[] = $value;
            else $this->data[$key] = $value;
        }
    }

    /**
     * 返回数据
     */
    public function end()
    {
        // 设置 HTTP code
        http_response_code($this->httpCode);

        // 设置 HTTP header
        $len = count($this->headers);

        for ($i = 0; $i < $len; $i ++) {
            header($this->headers[$i]);
        }

        // 指明不要返回任何内容
        if ($this->httpCode === HttpCode::NO_CONTENT) {
            echo NULL;
            exit();
        }

        // 设置返回状态码
        $response = [
            'code' => $this->code,
        ];

        // 设置返回数据
        if ($this->code === self::CODE_SUCCESS) $response['data'] = $this->data;
        else $response['error'] = $this->error;

        // JSON_FORCE_OBJECT 将数组转为 {}
        // JSON_UNESCAPED_UNICODE 保持中文数据，不转 \uxxxx 类型
        $json = json_encode($response, JSON_UNESCAPED_UNICODE);
        
        // json 转字符串错误，抛出异常
        if ($json === false) {
            throw new Exception('json_last_error: '. json_last_error());
        }

        echo $json;

        // 暂时先中断程序。如果以后有特殊情况（返回数据后 PHP 还需要做额外的操作）
        exit();
    }

    /**
     * 结束本次请求
     * 
     * 不直接 `exit()`，可以直接运行某些后台任务。为 Middleware::fallback 提供可能
     * 
     * Internally, the HttpKernel makes use of the fastcgi_finish_request PHP function. This means
     * that at the moment, only the PHP FPM server API is able to send a response to the client
     * while the server’s PHP process still performs some tasks. With all other server APIs,
     * listeners to kernel.terminate are still executed, but the response is not sent to the client
     * until they are all completed.
     * @see https://symfony.com/doc/current/components/http_kernel.html#component-http-kernel-kernel-terminate
     * 
     * 只有 php-fpm 可以实现这个功能，其它诸如 mod_php/fastcgi.exe 都不支持。
     * 根本原因还是 PHP 没有内建的服务器，只是设计成一门解释性语言，没有请求的概念，只有
     * 一个请求过程，导致多平台上对其实现不一。
     * 
     * 目前也有基于 PHP 第三方支持服务器的，比如 Swoole。
     * 还有新的 php-pm 进程管理器（适用于各大框架）、facebook/hhvm 虚拟机
     */
    public static function finishRequest($data = null)
    {
        // php-fpm
        if (function_exists('fastcgi_finish_request')) {
            echo $data;
            session_write_close();
            fastcgi_finish_request();
            return;
        }

        // Apache + mod_php
        // Nginx + php-cgi.exe

        if (ob_get_level() > 0) {
            ob_end_clean(); // nginx
        }
        ob_start();
        echo $data;
        header('Connection: close');
        header("X-Accel-Buffering: no"); // nginx
        header('Content-Length: '. ob_get_length());
        ob_end_flush();
        flush();

        // FIXME: 使用耗时的操作将导致后续一切 nginx 请求挂起
        // sleep(3);
    }
}
