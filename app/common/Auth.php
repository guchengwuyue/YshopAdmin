<?php
declare(strict_types=1);

namespace app\common;

use think\facade\Db;
use think\facade\Session;

/**
 * 登录用户与权限
 */
class Auth
{
    public const SESSION_USER = 'login_user';
    public const SESSION_PERMS = 'permissions';
    public const SESSION_ROLES = 'roles';

    protected static bool $tokenBound = false;

    /** @var array<string, mixed>|null */
    protected static ?array $tokenUser = null;

    /** @var array<int, string>|null */
    protected static ?array $tokenPerms = null;

    /** @var array<int, mixed>|null */
    protected static ?array $tokenRoles = null;

    /**
     * 绑定当前请求的管理员 Bearer 身份（不写 Session）
     * @param array<string, mixed> $user
     * @param array<int, string> $permissions
     * @param array<int, mixed> $roles
     */
    public static function bindToken(array $user, array $permissions = [], array $roles = []): void
    {
        self::$tokenBound = true;
        self::$tokenUser = $user;
        self::$tokenPerms = $permissions;
        self::$tokenRoles = $roles;
    }

    public static function usingToken(): bool
    {
        return self::$tokenBound;
    }

    /**
     * 获取当前登录用户
     */
    public static function getLoginUser(): ?array
    {
        if (self::$tokenBound) {
            return self::$tokenUser;
        }
        $user = Session::get(self::SESSION_USER);
        return is_array($user) ? $user : null;
    }

    /**
     * 写入登录用户及权限、角色到会话
     * @param array $permissions 权限标识列表
     * @param array $roles 角色列表
     */
    public static function setLoginUser(array $user, array $permissions = [], array $roles = []): void
    {
        Session::set(self::SESSION_USER, $user);
        Session::set(self::SESSION_PERMS, $permissions);
        Session::set(self::SESSION_ROLES, $roles);
    }

    /**
     * 获取当前用户 ID
     */
    public static function getUserId(): int
    {
        $user = self::getLoginUser();
        if (!$user) {
            return 0;
        }
        return (int) ($user['user_id'] ?? $user['userId'] ?? 0);
    }

    /**
     * 获取当前登录账号
     */
    public static function getLoginName(): string
    {
        $user = self::getLoginUser();
        if (!$user) {
            return '';
        }
        return (string) ($user['login_name'] ?? $user['loginName'] ?? '');
    }

    /**
     * 是否超级管理员
     * @param int|null $userId 指定用户 ID，默认当前用户
     */
    public static function isAdmin(?int $userId = null): bool
    {
        $uid = $userId ?? self::getUserId();
        if ($uid === 1) {
            return true;
        }
        // 兼容会话字段异常时的超管判断
        return self::getLoginName() === 'admin';
    }

    /**
     * 从数据库重载权限到会话（防止会话权限丢失/不完整导致误 403）
     */
    public static function reloadPermissions(): void
    {
        $userId = self::getUserId();
        if ($userId <= 0) {
            return;
        }
        if ($userId === 1 || self::getLoginName() === 'admin') {
            self::storePermissions(['*:*:*']);
            return;
        }
        $roleIds = Db::name('sys_user_role')->where('user_id', $userId)->column('role_id');
        if (!$roleIds) {
            self::storePermissions([]);
            return;
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
        self::storePermissions(array_values($result));
    }

    /**
     * 获取权限列表（Token 优先，否则会话）
     */
    public static function getPermissions(): array
    {
        if (self::$tokenBound) {
            return self::$tokenPerms ?? [];
        }
        $perms = Session::get(self::SESSION_PERMS);
        return is_array($perms) ? $perms : [];
    }

    /**
     * 获取角色列表（Token 优先，否则会话）
     */
    public static function getRoles(): array
    {
        if (self::$tokenBound) {
            return self::$tokenRoles ?? [];
        }
        $roles = Session::get(self::SESSION_ROLES);
        return is_array($roles) ? $roles : [];
    }

    /**
     * @param array<int, string> $permissions
     */
    protected static function storePermissions(array $permissions): void
    {
        if (self::$tokenBound) {
            self::$tokenPerms = $permissions;
            return;
        }
        Session::set(self::SESSION_PERMS, $permissions);
    }

    /**
     * 是否具备指定权限
     * @param string $perm 权限标识，支持逗号分隔多个
     */
    public static function hasPermi(string $perm): bool
    {
        if ($perm === '') {
            return true;
        }
        if (self::isAdmin()) {
            return true;
        }
        $permissions = self::getPermissions();
        if ($permissions === [] && self::getUserId() > 0) {
            self::reloadPermissions();
            $permissions = self::getPermissions();
        }
        if (in_array('*:*:*', $permissions, true) || in_array($perm, $permissions, true)) {
            return true;
        }
        foreach (explode(',', $perm) as $p) {
            $p = trim($p);
            if ($p !== '' && in_array($p, $permissions, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 退出登录并清空会话
     */
    public static function logout(): void
    {
        self::$tokenBound = false;
        self::$tokenUser = null;
        self::$tokenPerms = null;
        self::$tokenRoles = null;
        Session::delete(self::SESSION_USER);
        Session::delete(self::SESSION_PERMS);
        Session::delete(self::SESSION_ROLES);
        Session::clear();
    }
}
