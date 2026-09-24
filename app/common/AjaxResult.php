<?php
declare(strict_types=1);

namespace app\common;

use think\Response;

/**
 * Ajax 统一响应。成功码为 200，废弃 code:0。
 */
class AjaxResult
{
    /**
     * 成功响应
     */
    public static function success(string $msg = 'success', $data = null, int $code = ApiCode::SUCCESS): Response
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
    public static function error(string $msg = 'error', int $code = ApiCode::ERROR, $data = null): Response
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
        return self::error($msg, ApiCode::WARN, $data);
    }

    /**
     * 未登录。/api 使用 HTTP 401；旧页面 Ajax 传 $status=200，避免 jQuery 把 401 当成网络错误。
     */
    public static function unauth(string $msg = '未登录或登录已过期', int $status = ApiCode::UNAUTHORIZED): Response
    {
        return json(['code' => ApiCode::UNAUTHORIZED, 'msg' => $msg], $status);
    }

    /**
     * 无权限
     */
    public static function forbidden(string $msg = '没有权限，请联系管理员授权', int $status = ApiCode::FORBIDDEN): Response
    {
        return json(['code' => ApiCode::FORBIDDEN, 'msg' => $msg], $status);
    }

    /**
     * 成功结果数组（非 Response）
     */
    public static function toArray(string $msg = 'success', $data = null, int $code = ApiCode::SUCCESS): array
    {
        $result = ['code' => $code, 'msg' => $msg];
        if ($data !== null) {
            $result['data'] = $data;
        }
        return $result;
    }
}
