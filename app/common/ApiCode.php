<?php
declare(strict_types=1);

namespace app\common;

/**
 * API 信封状态码（body.code，不是旧版 code:0）
 */
class ApiCode
{
    public const SUCCESS = 200;
    public const WARN = 301;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const ERROR = 500;
}
