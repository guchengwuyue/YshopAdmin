<?php
declare(strict_types=1);

namespace app\middleware;

use app\common\AjaxResult;
use app\service\ConfigService;
use think\Request;

/**
 * 演示模式中间件 — 开启后禁止写操作，放行 GET 与只读查询白名单
 */
class DemoMode
{
    /**
     * 演示环境写操作拦截
     */
    public function handle(Request $request, \Closure $next)
    {
        //$enabled = (new ConfigService())->getKey('sys.demo.enabled', 'false') === 'true';
        //echo 'enabled: ' . (new ConfigService())->getKey('sys.demo.enabled', 'false');
        $enabled = env('APP_DEBUG', false);
        //echo 'enabled: ' . $enabled;
        //die;
        if ($enabled) {
            return $next($request);
        }

        if (strtoupper($request->method()) === 'GET') {
            return $next($request);
        }

        $path = '/' . ltrim((string) $request->pathinfo(), '/');
        if ($this->isWhitelisted($path)) {
            return $next($request);
        }

        return AjaxResult::error('演示环境模式禁止操作');
    }

    /**
     * POST 白名单：列表、唯一性校验、只读导出
     */
    private function isWhitelisted(string $path): bool
    {
        if (str_ends_with($path, '/list') || str_ends_with($path, '/export')) {
            return true;
        }
        // checkLoginNameUnique / checkRoleNameUnique 等只读校验
        return preg_match('#/check[A-Za-z]+$#', $path) === 1;
    }
}
