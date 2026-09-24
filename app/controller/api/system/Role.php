<?php
declare(strict_types=1);

namespace app\controller\api\system;

use app\common\Auth;
use app\controller\BaseController;
use app\service\DeptService;
use app\service\MenuService;
use app\service\RoleService;

/**
 * 角色管理 JSON API
 */
class Role extends BaseController
{
    protected RoleService $roleService;

    protected function initialize()
    {
        $this->roleService = new RoleService();
    }

    public function list()
    {
        [$rows, $total] = $this->roleService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    public function get($id = 0)
    {
        $role = $this->roleService->get((int) ($id ?: $this->request->param('id', 0)));
        if (!$role) {
            return $this->error('角色不存在');
        }
        return $this->success('success', $role);
    }

    public function add()
    {
        try {
            $id = $this->roleService->insert($this->request->param());
            return $this->toAjax($id > 0 ? 1 : 0);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function edit()
    {
        try {
            return $this->toAjax($this->roleService->update($this->request->param()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function remove()
    {
        try {
            return $this->toAjax($this->roleService->delete((string) $this->request->param('ids', '')));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function changeStatus()
    {
        try {
            $roleId = (int) $this->request->param('roleId', 0);
            $status = (string) $this->request->param('status', '0');
            return $this->toAjax($this->roleService->changeStatus($roleId, $status));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function authDataScope()
    {
        try {
            return $this->toAjax($this->roleService->authDataScope($this->request->param()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function menuTree()
    {
        if (!Auth::hasPermi('system:role:add') && !Auth::hasPermi('system:role:edit')) {
            return $this->error('没有权限，请联系管理员授权', 403);
        }
        $roleId = (int) $this->request->param('roleId', 0);
        return $this->success('success', (new MenuService())->roleMenuTreeData($roleId > 0 ? $roleId : null));
    }

    public function deptTree()
    {
        if (!Auth::hasPermi('system:role:edit')) {
            return $this->error('没有权限，请联系管理员授权', 403);
        }
        $roleId = (int) $this->request->param('roleId', 0);
        return $this->success('success', (new DeptService())->treeData($roleId > 0 ? $roleId : null));
    }
}
