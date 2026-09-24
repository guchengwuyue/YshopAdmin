<?php
declare(strict_types=1);

namespace app\controller\api;

use app\common\MemberContext;
use app\common\Token;
use app\controller\BaseController;
use app\service\MemberAuthService;
use think\facade\Session;

/**
 * 站点网关会员登录（index.php /api/member/*）。只返回 JSON，禁止 View::fetch。
 */
class Member extends BaseController
{
    public function login()
    {
        $username = (string) $this->request->param('username', $this->request->param('account', ''));
        $password = (string) $this->request->param('password', '');
        $keep = (string) $this->request->param('keeplogin', '') === '1';
        $result = (new MemberAuthService())->login($username, $password, $keep);
        if (!$result['ok']) {
            return $this->error($result['msg']);
        }
        $data = [
            'token'     => $result['token'],
            'tokenType' => $result['tokenType'] ?? 'Bearer',
            'expiresIn' => (int) ($result['expiresIn'] ?? 0),
        ];
        if (!empty($result['member'])) {
            $data['member'] = $result['member'];
        }
        return $this->success($result['msg'], $data);
    }

    public function register()
    {
        try {
            $this->assertCaptcha();
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
        $result = (new MemberAuthService())->register([
            'username' => (string) $this->request->param('username', ''),
            'password' => (string) $this->request->param('password', ''),
            'nickname' => (string) $this->request->param('nickname', ''),
            'email'    => (string) $this->request->param('email', ''),
            'mobile'   => (string) $this->request->param('mobile', ''),
        ]);
        if (!$result['ok']) {
            return $this->error($result['msg']);
        }
        return $this->success($result['msg'], [
            'token'     => $result['token'],
            'tokenType' => $result['tokenType'] ?? 'Bearer',
            'expiresIn' => (int) ($result['expiresIn'] ?? 0),
            'member'    => $result['member'] ?? null,
        ]);
    }

    /**
     * 校验图片验证码（注册等）
     */
    protected function assertCaptcha(): void
    {
        $code = trim((string) $this->request->param('captcha', ''));
        $expect = Session::get('KAPTCHA_SESSION_KEY');
        if ($expect === null || $expect === '' || $code === '' || strcasecmp($code, (string) $expect) !== 0) {
            throw new \InvalidArgumentException('验证码不正确');
        }
        Session::delete('KAPTCHA_SESSION_KEY');
    }

    public function profile()
    {
        $id = MemberContext::id();
        if ($id <= 0) {
            return $this->error('未登录或登录已过期', 401);
        }
        $result = (new MemberAuthService())->updateProfile([
            'id'       => $id,
            'nickname' => (string) $this->request->param('nickname', ''),
            'email'    => (string) $this->request->param('email', ''),
            'mobile'   => (string) $this->request->param('mobile', ''),
        ]);
        if (!$result['ok']) {
            return $this->error($result['msg']);
        }
        return $this->success($result['msg'], $result['member'] ?? null);
    }

    public function changepwd()
    {
        $id = MemberContext::id();
        if ($id <= 0) {
            return $this->error('未登录或登录已过期', 401);
        }
        $result = (new MemberAuthService())->changePassword(
            $id,
            (string) $this->request->param('oldpassword', ''),
            (string) $this->request->param('newpassword', ''),
            (string) $this->request->param('renewpassword', '')
        );
        if (!$result['ok']) {
            return $this->error($result['msg']);
        }
        return $this->success($result['msg']);
    }

    public function logout()
    {
        Token::revoke(Token::bearer($this->request));
        MemberContext::clear();
        return $this->success('退出成功');
    }

    public function getInfo()
    {
        $id = MemberContext::id();
        if ($id <= 0) {
            return $this->error('未登录或登录已过期', 401);
        }
        if (!class_exists(\addons\yscms\library\MemberAuth::class)) {
            require_once root_path() . 'addons/yscms/common.php';
        }
        $member = \addons\yscms\library\MemberAuth::getById($id);
        if (!$member) {
            return $this->error('未登录或登录已过期', 401);
        }
        return $this->success('success', \app\common\PageHelper::camelKeys($member));
    }
}
