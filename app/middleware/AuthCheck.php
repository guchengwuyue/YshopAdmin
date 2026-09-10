<?php
declare(strict_types=1);

namespace app\middleware;

use app\common\AjaxResult;
use app\common\Auth as AuthHelper;
use app\service\OnlineUserService;
use think\facade\Session;
use think\Request;

/**
 * 登录鉴权中间件（并刷新在线用户最后访问时间）
 */
class AuthCheck
{
    /**
     * 校验登录态，未登录返回 JSON 或跳转登录页
     */
    public function handle(Request $request, \Closure $next)
    {
        $loggedIn = (bool) AuthHelper::getLoginUser();
        $isAjax = $request->isAjax()
            || str_contains((string) $request->header('accept'), 'application/json')
            || (bool) $request->param('ajax')
            || str_ends_with($request->pathinfo(), '/list');

        if (!$loggedIn) {
            if ($isAjax) {
                return AjaxResult::unauth('登录超时，请重新登录');
            }

            $url = (string) url('/login');
            $msg = '登录超时，请重新登录';
            // 页面/iframe：先提示，再顶层跳转登录页
            $html = '<!DOCTYPE html><html lang="zh"><head><meta charset="utf-8">'
                . '<meta http-equiv="Cache-Control" content="no-store">'
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
        try {
            (new OnlineUserService())->touch(Session::getId());
        } catch (\Throwable $e) {
        }
        return $next($request);
    }
}
