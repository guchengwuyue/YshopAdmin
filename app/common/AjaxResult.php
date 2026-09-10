<?php
declare(strict_types=1);

namespace app\common;

use think\Response;

/**
 * Ajax 统一响应
 */
class AjaxResult
{
    /**
     * 成功响应
     */
    public static function success(string $msg = 'success', $data = null, int $code = 0): Response
    {
        $result = ['code' => $code, 'msg' => $msg];
        if ($data !== null) {
            $result['data'] = $data;
        }
        return json($result);
    }

    /**
     * 失败响应
     */
    public static function error(string $msg = 'error', int $code = 500, $data = null): Response
    {
        $result = ['code' => $code, 'msg' => $msg];
        if ($data !== null) {
            $result['data'] = $data;
        }
        return json($result);
    }

    /**
     * 警告响应（code=301）
     */
    public static function warn(string $msg = 'warn', $data = null): Response
    {
        return self::error($msg, 301, $data);
    }

    /**
     * 未登录 / 登录超时响应
     */
    public static function unauth(string $msg = '登录超时，请重新登录'): Response
    {
        return json(['code' => '1', 'msg' => $msg]);
    }

    /**
     * 成功结果数组（非 Response）
     */
    public static function toArray(string $msg = 'success', $data = null, int $code = 0): array
    {
        $result = ['code' => $code, 'msg' => $msg];
        if ($data !== null) {
            $result['data'] = $data;
        }
        return $result;
    }
}
