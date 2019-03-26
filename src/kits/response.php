<?php
  class Response {
    /**
     * 返回给前端的 code 标识
     * 
     * @var Number
     */
    public static $codeSuccess = 1; // 响应成功
    public static $codeFail = 0; // 响应失败，一般是客户端错误
    public static $codeError = -1; // 响应失败，服务端错误

    /**
     * 初始化
     * 
     * @param Number $code HTTP 状态码
     * @param Array|Object $data 请求成功的数据（简写方式，也可通过 addData 添加）
     */
    function __construct($code, $data = []) {
      // 定义返回 code
      if ($code >= 500) $this->code = self::$codeError;
      else if ($code >= 400) $this->code = self::$codeFail;
      else $this->code = self::$codeSuccess;

      $this->httpCode = $code;
      // 默认返回 json 格式
      $this->headers = ['Content-Type: application/json'];
      $this->data = (array) $data;
    }

    /**
     * 重置给前端的 code 标识。正常情况下不应该使用
     * 
     * @param Number $resCode
     */
    public function resetResCode($resCode) {
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
     */
    public function addHeader($header) {
      if (!is_array($this->headers)) $this->headers = [];

      if (is_string($header)) {
        // 字符串类型
        $this->headers[] = $header;
        return;
      }

      if (is_array($header)) {
        // 数组类型
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
    public function setErrorMsg($msg = '请求失败') {
      $this->error = $msg;
    }

    /**
     * 添加返回数据
     * 
     * @param Array $data
     */
    public function appendData($data = []) {
      foreach ($data as $key => $value) {
        if (is_int($key)) $this->data[] = $value;
        else $this->data[$key] = $value;
      }
    }

    // 返回数据
    public function end() {
      // 设置返回状态码
      $response = [
        'code' => $this->code,
      ];

      // 设置返回数据
      if ($this->code === self::$codeSuccess) $response['data'] = $this->data;
      else $response['error'] = $this->error;

      // 设置 HTTP code
      http_response_code($this->httpCode);

      // 设置 HTTP header
      for ($i = 0; $i < count($this->headers) - 1; $i ++) {
        header($this->headers[$i]);
      }

      echo json_encode($response, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
      // 暂时先中断程序。如果以后有特殊情况（返回数据后 PHP 还需要做额外的操作）
      exit();
    }
  }
