<?php
declare(strict_types=1);

namespace app\middleware;

use app\common\AjaxResult;
use think\Request;
use think\Response;

/**
 * /api 目标态约束：只允许 JSON。禁止 Think 模板（View::fetch）等 HTML 响应。
 */
class JsonOnly
{
    public function handle(Request $request, \Closure $next)
    {
        $response = $next($request);
        if (!$response instanceof Response) {
            return AjaxResult::error('API routes must return JSON');
        }
        $type = strtolower((string) $response->getHeader('Content-Type'));
        if ($type !== '' && !str_contains($type, 'json') && !str_contains($type, 'octet-stream')) {
            return AjaxResult::error('View::fetch is disabled on API routes');
        }
        return $response;
    }
}
