<?php
declare(strict_types=1);

namespace app\service;

use app\common\Auth;
use app\common\PageHelper;
use app\common\TreeUtils;
use think\facade\Db;

/**
 * 部门管理
 */
class DeptService
{
    /**
     * 查询部门列表
     * @param array $params 筛选条件（deptName / status）
     */
    public function selectList(array $params = []): array
    {
        $query = Db::name('sys_dept')->where('del_flag', '0');
        $deptName = $params['deptName'] ?? $params['dept_name'] ?? '';
        if ($deptName !== '') {
            $query->whereLike('dept_name', '%' . $deptName . '%');
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        $list = $query->order('parent_id, order_num')->select()->toArray();
        return PageHelper::camelRows($list);
    }

    /**
     * 查询部门树（下拉选择）
     */
    public function selectDeptTree(): array
    {
        $list = Db::name('sys_dept')->where('del_flag', '0')->where('status', '0')
            ->order('parent_id, order_num')->select()->toArray();
        return TreeUtils::buildDeptTree($list);
    }

    /**
     * 部门树数据（ztree，可按角色勾选）
     * @param int|null $roleId 角色 ID（可选，用于回显已授权部门）
     * @param int $excludeId 排除的部门 ID（含其下级）
     */
    public function treeData(?int $roleId = null, int $excludeId = 0): array
    {
        $list = Db::name('sys_dept')->where('del_flag', '0')->where('status', '0')
            ->order('parent_id, order_num')->select()->toArray();
        if ($excludeId > 0) {
            $list = array_values(array_filter($list, static function ($d) use ($excludeId) {
                if ((int) $d['dept_id'] === $excludeId) {
                    return false;
                }
                $ancestors = explode(',', (string) ($d['ancestors'] ?? ''));
                return !in_array((string) $excludeId, $ancestors, true);
            }));
        }
        $checked = [];
        if ($roleId) {
            $checked = array_map('intval', Db::name('sys_role_dept')->where('role_id', $roleId)->column('dept_id'));
        }
        $nodes = [];
        foreach ($list as $d) {
            $nodes[] = [
                'id'      => (int) $d['dept_id'],
                'pId'     => (int) $d['parent_id'],
                'name'    => $d['dept_name'],
                'title'   => $d['dept_name'],
                'checked' => in_array((int) $d['dept_id'], $checked, true),
                'open'    => false,
            ];
        }
        return $nodes;
    }

    /**
     * 根据部门 ID 查询详情
     */
    public function get(int $deptId): ?array
    {
        $row = Db::name('sys_dept')->where('dept_id', $deptId)->where('del_flag', '0')->find();
        return $row ? PageHelper::camelKeys($row) : null;
    }

    /**
     * 新增部门
     */
    public function insert(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $parentId = (int) ($data['parent_id'] ?? 0);
        $ancestors = '0';
        if ($parentId > 0) {
            $parent = Db::name('sys_dept')->where('dept_id', $parentId)->find();
            if ($parent) {
                $ancestors = $parent['ancestors'] . ',' . $parentId;
            }
        }
        $row = [
            'parent_id'   => $parentId,
            'ancestors'   => $ancestors,
            'dept_name'   => $data['dept_name'] ?? '',
            'order_num'   => $data['order_num'] ?? 0,
            'leader'      => $data['leader'] ?? '',
            'phone'       => $data['phone'] ?? '',
            'email'       => $data['email'] ?? '',
            'status'      => $data['status'] ?? '0',
            'del_flag'    => '0',
            'create_by'   => Auth::getLoginName(),
            'create_time' => date('Y-m-d H:i:s'),
        ];
        return (int) Db::name('sys_dept')->insertGetId($row);
    }

    /**
     * 修改部门
     */
    public function update(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $deptId = (int) ($data['dept_id'] ?? 0);
        $parentId = (int) ($data['parent_id'] ?? 0);
        if ($parentId === $deptId) {
            throw new \RuntimeException('上级部门不能是自己');
        }
        $ancestors = '0';
        if ($parentId > 0) {
            $parent = Db::name('sys_dept')->where('dept_id', $parentId)->find();
            if ($parent) {
                $ancestors = $parent['ancestors'] . ',' . $parentId;
            }
        }
        $old = Db::name('sys_dept')->where('dept_id', $deptId)->find();
        $row = [
            'parent_id'   => $parentId,
            'ancestors'   => $ancestors,
            'dept_name'   => $data['dept_name'] ?? '',
            'order_num'   => $data['order_num'] ?? 0,
            'leader'      => $data['leader'] ?? '',
            'phone'       => $data['phone'] ?? '',
            'email'       => $data['email'] ?? '',
            'status'      => $data['status'] ?? '0',
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ];
        $n = Db::name('sys_dept')->where('dept_id', $deptId)->update($row);
        if ($old && $old['ancestors'] !== $ancestors) {
            $children = Db::name('sys_dept')->whereRaw('FIND_IN_SET(?, ancestors)', [$deptId])->select();
            foreach ($children as $child) {
                $newAncestors = str_replace($old['ancestors'] . ',' . $deptId, $ancestors . ',' . $deptId, $child['ancestors']);
                Db::name('sys_dept')->where('dept_id', $child['dept_id'])->update(['ancestors' => $newAncestors]);
            }
        }
        return $n;
    }

    /**
     * 删除部门（逻辑删除）
     */
    public function delete(int $deptId): int
    {
        if (Db::name('sys_dept')->where('parent_id', $deptId)->where('del_flag', '0')->count() > 0) {
            throw new \RuntimeException('存在下级部门,不允许删除');
        }
        if (Db::name('sys_user')->where('dept_id', $deptId)->where('del_flag', '0')->count() > 0) {
            throw new \RuntimeException('部门存在用户,不允许删除');
        }
        return Db::name('sys_dept')->where('dept_id', $deptId)->update([
            'del_flag'    => '2',
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }
}
