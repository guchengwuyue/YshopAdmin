<?php
declare(strict_types=1);

namespace app\service;

use app\common\Auth;
use app\common\PageHelper;
use app\common\PasswordUtils;
use app\common\UserAgent;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;

/**
 * 登录业务
 */
class LoginService
{
    /**
     * 用户登录
     * @param string|null $validateCode 验证码（会话有验证码时必填）
     */
    public function login(string $username, string $password, ?string $validateCode = null): array
    {
        $username = trim($username);
        if ($username === '' || $password === '') {
            $this->recordLogin($username, '0', 'empty credentials');
            return ['ok' => false, 'msg' => 'username/password required'];
        }

        $retry = new PasswordRetryService();
        if ($retry->isLocked($username)) {
            $this->recordLogin($username, '0', 'password retry limit');
            return ['ok' => false, 'msg' => 'account locked, contact admin to unlock'];
        }

        $captcha = Session::get('KAPTCHA_SESSION_KEY');
        if ($captcha !== null && $captcha !== '') {
            if ($validateCode === null || strcasecmp((string) $validateCode, (string) $captcha) !== 0) {
                $this->recordLogin($username, '0', 'captcha error');
                return ['ok' => false, 'msg' => 'captcha error'];
            }
            Session::delete('KAPTCHA_SESSION_KEY');
        }

        $user = Db::name('sys_user')
            ->where('login_name', $username)
            ->where('del_flag', '0')
            ->find();

        if (!$user) {
            $retry->recordFail($username);
            $this->recordLogin($username, '0', 'user not found');
            return ['ok' => false, 'msg' => 'user not found or bad password'];
        }

        if ((string) $user['status'] === '1') {
            $this->recordLogin($username, '0', 'user disabled');
            return ['ok' => false, 'msg' => 'user disabled'];
        }

        if (!PasswordUtils::matches($username, $password, (string) $user['salt'], (string) $user['password'])) {
            $retry->recordFail($username);
            $this->recordLogin($username, '0', 'bad password');
            return ['ok' => false, 'msg' => 'user not found or bad password'];
        }

        $retry->clear($username);

        $userId = (int) $user['user_id'];
        $roles = $this->loadRoles($userId);
        $permissions = $this->loadPermissions($userId, $roles);

        unset($user['password'], $user['salt']);
        $user = PageHelper::camelKeys($user);
        Auth::setLoginUser($user, $permissions, PageHelper::camelRows($roles));

        Db::name('sys_user')->where('user_id', $userId)->update([
            'login_ip'   => Request::ip(),
            'login_date' => date('Y-m-d H:i:s'),
        ]);

        try {
            (new OnlineUserService())->saveOnline($username);
        } catch (\Throwable $e) {
        }

        $this->recordLogin($username, '1', 'login success');
        return ['ok' => true, 'msg' => 'login success'];
    }

    /**
     * 退出登录
     */
    public function logout(): void
    {
        $name = Auth::getLoginName();
        $sid = Session::getId();
        if ($name !== '') {
            $this->recordLogin($name, '1', 'logout');
        }
        try {
            (new OnlineUserService())->removeBySession($sid);
        } catch (\Throwable $e) {
        }
        Auth::logout();
    }

    /**
     * 加载用户角色列表
     */
    protected function loadRoles(int $userId): array
    {
        if ($userId === 1) {
            return Db::name('sys_role')->where('del_flag', '0')->where('status', '0')->select()->toArray();
        }
        return Db::name('sys_role')->alias('r')
            ->join('sys_user_role ur', 'ur.role_id = r.role_id')
            ->where('ur.user_id', $userId)
            ->where('r.del_flag', '0')
            ->where('r.status', '0')
            ->field('r.*')
            ->select()
            ->toArray();
    }

    /**
     * 加载用户权限标识列表
     */
    protected function loadPermissions(int $userId, array $roles): array
    {
        if ($userId === 1) {
            return ['*:*:*'];
        }
        $roleIds = array_column($roles, 'role_id');
        if (!$roleIds) {
            return [];
        }
        $perms = Db::name('sys_menu')->alias('m')
            ->join('sys_role_menu rm', 'm.menu_id = rm.menu_id')
            ->whereIn('rm.role_id', $roleIds)
            ->where('m.perms', '<>', '')
            ->whereNotNull('m.perms')
            ->column('m.perms');
        $result = [];
        foreach ($perms as $p) {
            foreach (explode(',', (string) $p) as $one) {
                $one = trim($one);
                if ($one !== '') {
                    $result[$one] = $one;
                }
            }
        }
        return array_values($result);
    }

    /**
     * 记录登录日志
     * @param string $status 状态（0失败 / 1成功）
     * @param string $msg 消息
     */
    protected function recordLogin(string $username, string $status, string $msg): void
    {
        try {
            $ua = (string) Request::header('user-agent');
            Db::name('sys_logininfor')->insert([
                'login_name'     => $username,
                'ipaddr'         => Request::ip(),
                'login_location' => '',
                'browser'        => UserAgent::browser($ua),
                'os'             => UserAgent::os($ua),
                'status'         => $status,
                'msg'            => $msg,
                'login_time'     => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
        }
    }
}
