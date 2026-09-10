<?php
declare(strict_types=1);

namespace app\middleware;

use app\common\AjaxResult;
use app\common\Auth;
use think\Request;

/**
 * 权限校验中间件 — 读取路由 option 'perms'
 */
class Permission
{
    /**
     * 校验当前用户是否具备指定权限
     * @param string|null $perms 权限标识，空则取路由 option
     */
    public function handle(Request $request, \Closure $next, ?string $perms = null)
    {
        if ($perms === null || $perms === '') {
            $rule = $request->rule();
            if ($rule) {
                $perms = (string) $rule->getOption('perms');
            }
        }
        if ($perms !== null && $perms !== '' && !Auth::hasPermi($perms)) {
            // 再从数据库重载一次，避免会话权限不完整误拦
            Auth::reloadPermissions();
        }
        if ($perms !== null && $perms !== '' && !Auth::hasPermi($perms)) {
            $isAjax = $request->isAjax() || str_contains((string) $request->header('accept'), 'application/json');
            if ($isAjax) {
                // 无权限接口：与登录超时区分，仍返回 403
                return AjaxResult::error('没有权限，请联系管理员授权', 403);
            }
            // 页面请求：提示后回登录（避免 iframe 里干瞪 403）
            $url = (string) url('/login');
            $msg = '登录超时或无权访问，请重新登录';
            $html = '<!DOCTYPE html><html lang="zh"><head><meta charset="utf-8">'
                . '<title>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</title>'
                . '<style>body{margin:0;font-family:"Microsoft YaHei",sans-serif;background:#f5f5f5;color:#333;'
                . 'display:flex;align-items:center;justify-content:center;min-height:100vh}'
                . '.box{background:#fff;padding:36px 48px;border-radius:8px;text-align:center;'
                . 'box-shadow:0 2px 12px rgba(0,0,0,.08)}.box h3{margin:0 0 12px;font-size:18px}'
                . '.box p{margin:0;color:#888;font-size:13px}</style></head><body>'
                . '<div class="box"><h3>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</h3>'
                . '<p>即将自动跳转到登录页...</p></div>'
                . '<script>setTimeout(function(){try{top.location.href=' . json_encode($url, JSON_UNESCAPED_SLASHES)
                . ';}catch(e){location.href=' . json_encode($url, JSON_UNESCAPED_SLASHES) . ';}},1200);</script>'
                . '</body></html>';
            return response($html, 401, ['Content-Type' => 'text/html; charset=utf-8']);
        }
        return $next($request);
    }
}
