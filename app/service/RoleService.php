<?php
declare(strict_types=1);

namespace app\service;

use app\common\Auth;
use app\common\PageHelper;
use think\facade\Db;

/**
 * 角色管理
 */
class RoleService
{
    /**
     * 查询角色列表
     * @param array $params 筛选条件（roleName / roleKey / status）
     * @param bool $export 是否导出（不分页）
     */
    public function selectList(array $params = [], bool $export = false): array
    {
        if ($export) {
            PageHelper::startExport();
        } else {
            PageHelper::startPage();
        }
        $query = Db::name('sys_role')->where('del_flag', '0');
        $roleName = $params['roleName'] ?? $params['role_name'] ?? '';
        if ($roleName !== '') {
            $query->whereLike('role_name', '%' . $roleName . '%');
        }
        $roleKey = $params['roleKey'] ?? $params['role_key'] ?? '';
        if ($roleKey !== '') {
            $query->whereLike('role_key', '%' . $roleKey . '%');
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        [$rows, $total] = PageHelper::paginate($query);
        return [PageHelper::camelRows($rows), $total];
    }

    /**
     * 根据角色 ID 查询详情（含菜单、部门）
     */
    public function get(int $roleId): ?array
    {
        $row = Db::name('sys_role')->where('role_id', $roleId)->where('del_flag', '0')->find();
        if (!$row) {
            return null;
        }
        $role = PageHelper::camelKeys($row);
        $role['menuIds'] = Db::name('sys_role_menu')->where('role_id', $roleId)->column('menu_id');
        $role['deptIds'] = Db::name('sys_role_dept')->where('role_id', $roleId)->column('dept_id');
        return $role;
    }

    /**
     * 新增角色
     */
    public function insert(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        if (!$this->checkRoleNameUnique((string) ($data['role_name'] ?? ''))) {
            throw new \RuntimeException('角色名称已存在');
        }
        if (!$this->checkRoleKeyUnique((string) ($data['role_key'] ?? ''))) {
            throw new \RuntimeException('角色权限已存在');
        }
        $menuIds = $this->parseIds($data['menuIds'] ?? $data['menu_ids'] ?? []);
        $row = [
            'role_name'   => $data['role_name'] ?? '',
            'role_key'    => $data['role_key'] ?? '',
            'role_sort'   => $data['role_sort'] ?? 0,
            'data_scope'  => $data['data_scope'] ?? '1',
            'status'      => $data['status'] ?? '0',
            'del_flag'    => '0',
            'remark'      => $data['remark'] ?? '',
            'create_by'   => Auth::getLoginName(),
            'create_time' => date('Y-m-d H:i:s'),
        ];
        $roleId = (int) Db::name('sys_role')->insertGetId($row);
        $this->insertRoleMenu($roleId, $menuIds);
        return $roleId;
    }

    /**
     * 修改角色
     */
    public function update(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $roleId = (int) ($data['role_id'] ?? 0);
        if ($roleId === 1) {
            throw new \RuntimeException('不允许操作超级管理员角色');
        }
        $menuIds = $this->parseIds($data['menuIds'] ?? $data['menu_ids'] ?? null);
        $row = [
            'role_name'   => $data['role_name'] ?? '',
            'role_key'    => $data['role_key'] ?? '',
            'role_sort'   => $data['role_sort'] ?? 0,
            'status'      => $data['status'] ?? '0',
            'remark'      => $data['remark'] ?? '',
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ];
        $n = Db::name('sys_role')->where('role_id', $roleId)->update($row);
        if ($menuIds !== null) {
            Db::name('sys_role_menu')->where('role_id', $roleId)->delete();
            $this->insertRoleMenu($roleId, $menuIds);
        }
        return $n;
    }

    /**
     * 删除角色（逻辑删除）
     * @param string $ids 角色 ID，逗号分隔
     */
    public function delete(string $ids): int
    {
        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        $idArr = array_values(array_filter($idArr, static fn($id) => $id !== 1));
        if (!$idArr) {
            return 0;
        }
        Db::name('sys_role_menu')->whereIn('role_id', $idArr)->delete();
        Db::name('sys_role_dept')->whereIn('role_id', $idArr)->delete();
        Db::name('sys_user_role')->whereIn('role_id', $idArr)->delete();
        return Db::name('sys_role')->whereIn('role_id', $idArr)->update([
            'del_flag'    => '2',
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 授权数据权限（数据范围 + 部门）
     */
    public function authDataScope(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $roleId = (int) ($data['role_id'] ?? 0);
        $deptIds = $this->parseIds($data['deptIds'] ?? $data['dept_ids'] ?? []) ?? [];
        Db::name('sys_role')->where('role_id', $roleId)->update([
            'data_scope'  => $data['data_scope'] ?? '1',
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
        Db::name('sys_role_dept')->where('role_id', $roleId)->delete();
        foreach ($deptIds as $deptId) {
            Db::name('sys_role_dept')->insert(['role_id' => $roleId, 'dept_id' => $deptId]);
        }
        return 1;
    }

    /**
     * 修改角色状态
     */
    public function changeStatus(int $roleId, string $status): int
    {
        if ($roleId === 1) {
            throw new \RuntimeException('不允许操作超级管理员角色');
        }
        return Db::name('sys_role')->where('role_id', $roleId)->update([
            'status'      => $status,
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 校验角色名称是否唯一
     * @param int|null $roleId 排除的角色 ID（修改时用）
     */
    public function checkRoleNameUnique(string $roleName, ?int $roleId = null): bool
    {
        $q = Db::name('sys_role')->where('role_name', $roleName)->where('del_flag', '0');
        if ($roleId) {
            $q->where('role_id', '<>', $roleId);
        }
        return $q->count() === 0;
    }

    /**
     * 校验角色权限字符是否唯一
     * @param int|null $roleId 排除的角色 ID（修改时用）
     */
    public function checkRoleKeyUnique(string $roleKey, ?int $roleId = null): bool
    {
        $q = Db::name('sys_role')->where('role_key', $roleKey)->where('del_flag', '0');
        if ($roleId) {
            $q->where('role_id', '<>', $roleId);
        }
        return $q->count() === 0;
    }

    /**
     * 查询全部角色
     */
    public function selectRolesAll(): array
    {
        return PageHelper::camelRows(
            Db::name('sys_role')->where('del_flag', '0')->order('role_sort')->select()->toArray()
        );
    }

    /**
     * 写入角色菜单关联
     */
    protected function insertRoleMenu(int $roleId, array $menuIds): void
    {
        foreach ($menuIds as $menuId) {
            Db::name('sys_role_menu')->insert(['role_id' => $roleId, 'menu_id' => (int) $menuId]);
        }
    }

    /**
     * 解析 ID 列表（支持数组或逗号分隔字符串；null 表示未传）
     */
    protected function parseIds($ids): ?array
    {
        if ($ids === null) {
            return null;
        }
        if (is_string($ids)) {
            $ids = $ids === '' ? [] : explode(',', $ids);
        }
        if (!is_array($ids)) {
            return [];
        }
        return array_values(array_filter(array_map('intval', $ids)));
    }
}
