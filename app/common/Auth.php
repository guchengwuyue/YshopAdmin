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

    /**
     * 获取当前登录用户
     */
    public static function getLoginUser(): ?array
    {
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
            Session::set(self::SESSION_PERMS, ['*:*:*']);
            return;
        }
        $roleIds = Db::name('sys_user_role')->where('user_id', $userId)->column('role_id');
        if (!$roleIds) {
            Session::set(self::SESSION_PERMS, []);
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
        Session::set(self::SESSION_PERMS, array_values($result));
    }

    /**
     * 获取会话中的权限列表
     */
    public static function getPermissions(): array
    {
        $perms = Session::get(self::SESSION_PERMS);
        return is_array($perms) ? $perms : [];
    }

    /**
     * 获取会话中的角色列表
     */
    public static function getRoles(): array
    {
        $roles = Session::get(self::SESSION_ROLES);
        return is_array($roles) ? $roles : [];
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
        Session::delete(self::SESSION_USER);
        Session::delete(self::SESSION_PERMS);
        Session::delete(self::SESSION_ROLES);
        Session::clear();
    }
}
