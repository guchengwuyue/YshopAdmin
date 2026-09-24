<?php
declare(strict_types=1);

namespace app\service;

use addons\yscms\library\MemberAuth;
use app\common\PageHelper;
use app\common\Token;
use think\facade\Db;

/**
 * 站点会员登录（独立于管理员 Session / adm_ Token）
 */
class MemberAuthService
{
    /**
     * 确保 yscms 助手函数已加载（register 依赖 yscms_config）
     */
    protected function ensureYscmsHelpers(): void
    {
        if (!function_exists('yscms_config')) {
            require_once root_path() . 'addons/yscms/common.php';
        }
    }

    /**
     * @return array{ok: bool, msg: string, token?: string, tokenType?: string, expiresIn?: int, member?: array}
     */
    public function login(string $username, string $password, bool $keep = true): array
    {
        $username = trim($username);
        if ($username === '' || $password === '') {
            return ['ok' => false, 'msg' => '请输入账号和密码'];
        }

        try {
            $row = Db::name('ys_cms_member')
                ->where('del_flag', '0')
                ->where(function ($query) use ($username) {
                    $query->where('username', $username);
                    if (str_contains($username, '@')) {
                        $query->whereOr('email', strtolower($username));
                    }
                    if (preg_match('/^1[3-9]\d{9}$/', $username)) {
                        $query->whereOr('mobile', $username);
                    }
                })
                ->find();
        } catch (\Throwable $e) {
            return ['ok' => false, 'msg' => '会员服务不可用'];
        }

        if (!$row || !password_verify($password, (string) ($row['password'] ?? ''))) {
            return ['ok' => false, 'msg' => '账号或密码错误'];
        }
        if ((string) ($row['status'] ?? '1') !== '0') {
            return ['ok' => false, 'msg' => '账号已停用'];
        }

        $this->ensureYscmsHelpers();
        $formatted = MemberAuth::getById((int) ($row['id'] ?? 0));
        if (!$formatted) {
            return ['ok' => false, 'msg' => '账号或密码错误'];
        }
        $member = PageHelper::camelKeys($formatted);
        // 保持会话 30 天，否则 2 小时
        $ttl = $keep ? 86400 * 30 : 7200;
        $issued = Token::issue(Token::REALM_MEMBER, ['member' => $member], $ttl);

        return [
            'ok'        => true,
            'msg'       => '登录成功',
            'token'     => $issued['token'],
            'tokenType' => $issued['tokenType'],
            'expiresIn' => $issued['expiresIn'],
            'member'    => $member,
        ];
    }

    /**
     * 注册并签发 mem_ Token
     * @param array<string, mixed> $data
     * @return array{ok: bool, msg: string, token?: string, tokenType?: string, expiresIn?: int, member?: array}
     */
    public function register(array $data): array
    {
        $this->ensureYscmsHelpers();
        try {
            $user = MemberAuth::register($data);
        } catch (\Throwable $e) {
            return ['ok' => false, 'msg' => $e->getMessage()];
        }

        $member = PageHelper::camelKeys($user);
        $issued = Token::issue(Token::REALM_MEMBER, ['member' => $member]);

        return [
            'ok'        => true,
            'msg'       => '注册成功',
            'token'     => $issued['token'],
            'tokenType' => $issued['tokenType'],
            'expiresIn' => $issued['expiresIn'],
            'member'    => $member,
        ];
    }

    /**
     * 更新资料（调用方传入 id）
     * @param array<string, mixed> $data
     * @return array{ok: bool, msg: string, member?: array}
     */
    public function updateProfile(array $data): array
    {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) {
            return ['ok' => false, 'msg' => '请先登录'];
        }
        $this->ensureYscmsHelpers();
        try {
            $user = MemberAuth::updateProfile($id, $data);
        } catch (\Throwable $e) {
            return ['ok' => false, 'msg' => $e->getMessage()];
        }

        return [
            'ok'     => true,
            'msg'    => '保存成功',
            'member' => PageHelper::camelKeys($user),
        ];
    }

    /**
     * 修改密码
     * @return array{ok: bool, msg: string}
     */
    public function changePassword(int $id, string $old, string $new, string $renew): array
    {
        if ($id <= 0) {
            return ['ok' => false, 'msg' => '请先登录'];
        }
        $this->ensureYscmsHelpers();
        try {
            MemberAuth::changePassword($id, $old, $new, $renew);
        } catch (\Throwable $e) {
            return ['ok' => false, 'msg' => $e->getMessage()];
        }

        return ['ok' => true, 'msg' => '密码修改成功'];
    }
}
