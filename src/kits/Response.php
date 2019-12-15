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
        'Access-Control-Allow-Origin: *',
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
}
