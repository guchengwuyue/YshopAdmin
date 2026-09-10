<?php
declare(strict_types=1);

namespace app\controller\system;

use app\controller\BaseController;
use app\service\MenuService;
use app\service\RoleService;
use think\facade\View;

/**
 * 角色管理
 */
class Role extends BaseController
{
    protected RoleService $roleService;

    protected function initialize()
    {
        $this->roleService = new RoleService();
    }

    /**
     * 角色列表页
     */
    public function index()
    {
        return View::fetch('role/role');
    }

    /**
     * 查询角色列表
     */
    public function list()
    {
        [$rows, $total] = $this->roleService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 新增角色
     */
    public function add()
    {
        if ($this->request->isPost()) {
            try {
                $id = $this->roleService->insert($this->request->post());
                return $this->toAjax($id > 0 ? 1 : 0);
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        return View::fetch('role/add');
    }

    /**
     * 修改角色
     * @param int $id 角色 ID
     */
    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            try {
                return $this->toAjax($this->roleService->update($this->request->post()));
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        $role = $this->roleService->get((int) $id);
        return View::fetch('role/edit', ['role' => $role]);
    }

    /**
     * 删除角色
     */
    public function remove()
    {
        try {
            return $this->toAjax($this->roleService->delete((string) $this->request->post('ids', '')));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 分配数据权限
     * @param int $id 角色 ID
     */
    public function authDataScope($id = 0)
    {
        if ($this->request->isPost()) {
            try {
                return $this->toAjax($this->roleService->authDataScope($this->request->post()));
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        $role = $this->roleService->get((int) $id);
        return View::fetch('role/dataScope', ['role' => $role]);
    }

    /**
     * 修改角色状态
     */
    public function changeStatus()
    {
        try {
            return $this->toAjax($this->roleService->changeStatus(
                (int) $this->request->post('roleId'),
                (string) $this->request->post('status')
            ));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 校验角色名称是否唯一
     */
    public function checkRoleNameUnique()
    {
        $ok = $this->roleService->checkRoleNameUnique(
            (string) $this->request->param('roleName', ''),
            $this->request->param('roleId') ? (int) $this->request->param('roleId') : null
        );
        return $ok ? 'true' : 'false';
    }

    /**
     * 校验权限字符是否唯一
     */
    public function checkRoleKeyUnique()
    {
        $ok = $this->roleService->checkRoleKeyUnique(
            (string) $this->request->param('roleKey', ''),
            $this->request->param('roleId') ? (int) $this->request->param('roleId') : null
        );
        return $ok ? 'true' : 'false';
    }

    /**
     * 角色菜单树数据
     */
    public function menuTreeData()
    {
        $roleId = $this->request->param('roleId');
        return json((new MenuService())->roleMenuTreeData($roleId ? (int) $roleId : null));
    }

    /**
     * 角色部门树数据（数据权限）
     */
    public function deptTreeData()
    {
        $roleId = $this->request->param('roleId');
        return json((new \app\service\DeptService())->treeData($roleId ? (int) $roleId : null));
    }

    /**
     * 导出角色
     */
    public function export()
    {
        [$rows] = $this->roleService->selectList($this->request->param(), true);
        $file = \app\common\ExcelUtil::export($rows, [
            'roleId' => 'Role ID',
            'roleName' => 'Role Name',
            'roleKey' => 'Role Key',
            'roleSort' => 'Sort',
            'status' => 'Status',
            'createTime' => 'Create Time',
        ], 'role');
        return $this->success($file);
    }
}
