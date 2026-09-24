<?php
declare(strict_types=1);

namespace app\middleware;

use app\common\AjaxResult;
use app\common\Auth;
use app\common\Token;
use think\Request;

/**
 * 管理端 API：只接受 adm_ Bearer，不回退 Session
 */
class AdminToken
{
    public function handle(Request $request, \Closure $next)
    {
        $token = Token::bearer($request);
        $subject = $token === '' ? null : Token::read($token, Token::REALM_ADMIN);
        if ($subject === null) {
            return AjaxResult::unauth('未登录或登录已过期');
        }
        Auth::bindToken(
            is_array($subject['user'] ?? null) ? $subject['user'] : [],
            is_array($subject['permissions'] ?? null) ? $subject['permissions'] : [],
            is_array($subject['roles'] ?? null) ? $subject['roles'] : []
        );
        return $next($request);
    }
}
