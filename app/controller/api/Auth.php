<?php
declare(strict_types=1);

namespace app\controller\api;

use app\common\Auth as AuthHelper;
use app\common\Token;
use app\controller\BaseController;
use app\service\LoginService;

/**
 * 管理网关登录（yshopadmin.php /api/*）。只返回 JSON，禁止 View::fetch。
 */
class Auth extends BaseController
{
    public function login()
    {
        $username = (string) $this->request->param('username', '');
        $password = (string) $this->request->param('password', '');
        $validateCode = $this->request->param('validateCode');
        $result = (new LoginService())->login(
            $username,
            $password,
            $validateCode !== null && $validateCode !== '' ? (string) $validateCode : null
        );
        if (!$result['ok']) {
            return $this->error($result['msg']);
        }
        if (($result['token'] ?? '') === '') {
            return $this->error('签发 Token 失败');
        }
        return $this->success($result['msg'], [
            'token'     => $result['token'],
            'tokenType' => 'Bearer',
            'expiresIn' => (int) ($result['expiresIn'] ?? 0),
        ]);
    }

    public function logout()
    {
        Token::revoke(Token::bearer($this->request));
        (new LoginService())->logout();
        return $this->success('退出成功');
    }

    public function getInfo()
    {
        $user = AuthHelper::getLoginUser();
        if (!$user) {
            return $this->error('未登录或登录已过期', 401);
        }
        return $this->success('success', [
            'user'        => $user,
            'roles'       => AuthHelper::getRoles(),
            'permissions' => AuthHelper::getPermissions(),
        ]);
    }
}
