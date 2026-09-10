<?php
declare(strict_types=1);

namespace app\common;

use think\db\BaseQuery;
use think\facade\Db;

/**
 * 数据权限过滤
 * data_scope: 1全部 2自定义 3本部门 4本部门及以下 5仅本人
 */
class DataScope
{
    /**
     * 按当前用户角色数据范围过滤查询
     * @param string $userAlias 用户表别名
     * @param string $deptAlias 部门表别名
     */
    public static function applyDataScope(BaseQuery $query, string $userAlias = 'u', string $deptAlias = 'd'): BaseQuery
    {
        if (Auth::isAdmin()) {
            return $query;
        }

        $user = Auth::getLoginUser();
        if (!$user) {
            return $query->whereRaw('1=0');
        }

        $userId = (int) ($user['user_id'] ?? $user['userId'] ?? 0);
        $deptId = (int) ($user['dept_id'] ?? $user['deptId'] ?? 0);
        $roles = Auth::getRoles();

        $scopes = [];
        foreach ($roles as $role) {
            $scopes[] = (string) ($role['data_scope'] ?? $role['dataScope'] ?? '5');
        }
        if (!$scopes) {
            $scopes = ['5'];
        }

        // 任一角色为全部数据权限则不过滤
        if (in_array('1', $scopes, true)) {
            return $query;
        }

        $conditions = [];
        $bind = [];

        foreach ($roles as $role) {
            $scope = (string) ($role['data_scope'] ?? $role['dataScope'] ?? '5');
            $roleId = (int) ($role['role_id'] ?? $role['roleId'] ?? 0);

            switch ($scope) {
                case '2': // 自定义
                    $conditions[] = "{$deptAlias}.dept_id IN (SELECT dept_id FROM sys_role_dept WHERE role_id = {$roleId})";
                    break;
                case '3': // 本部门
                    $conditions[] = "{$deptAlias}.dept_id = {$deptId}";
                    break;
                case '4': // 本部门及以下
                    $conditions[] = "({$deptAlias}.dept_id = {$deptId} OR FIND_IN_SET({$deptId}, {$deptAlias}.ancestors))";
                    break;
                case '5': // 仅本人
                default:
                    $conditions[] = "{$userAlias}.user_id = {$userId}";
                    break;
            }
        }

        if ($conditions) {
            $query->whereRaw('(' . implode(' OR ', array_unique($conditions)) . ')', $bind);
        }

        return $query;
    }
}
