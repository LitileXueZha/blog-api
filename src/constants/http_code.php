<?php

/**
 * HTTP 状态码定义
 */

class HttpCode
{
    // 成功
    const OK = 200;

    // 重定向
    const MOVED_PERMANENTLY = 301;
    const NOT_MODIFIED = 304;

    // 客户端错误
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;

    // 服务端错误
    const INTERNAL_SERVER_ERROR = 500;
    const SERVICE_UNAVALIABLED = 503;
}
