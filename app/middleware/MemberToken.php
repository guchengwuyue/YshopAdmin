<?php
declare(strict_types=1);

namespace app\middleware;

use app\common\AjaxResult;
use app\common\MemberContext;
use app\common\Token;
use think\Request;

/**
 * 站点会员 API：只接受 mem_ Bearer，与管理员 Token 隔离
 */
class MemberToken
{
    public function handle(Request $request, \Closure $next)
    {
        $token = Token::bearer($request);
        $subject = $token === '' ? null : Token::read($token, Token::REALM_MEMBER);
        $member = is_array($subject['member'] ?? null) ? $subject['member'] : null;
        if ($member === null || (int) ($member['id'] ?? 0) <= 0) {
            return AjaxResult::unauth('未登录或登录已过期');
        }
        MemberContext::bind($member);
        return $next($request);
    }
}
