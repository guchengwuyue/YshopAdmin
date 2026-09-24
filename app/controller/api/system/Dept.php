<?php
declare(strict_types=1);

namespace app\controller\api\system;

use app\common\Auth;
use app\controller\BaseController;
use app\service\DeptService;

/**
 * 部门管理 JSON API
 */
class Dept extends BaseController
{
    protected DeptService $deptService;

    protected function initialize()
    {
        $this->deptService = new DeptService();
    }

    public function list()
    {
        return $this->success('success', $this->deptService->selectList($this->request->param()));
    }

    public function get($id = 0)
    {
        $dept = $this->deptService->get((int) ($id ?: $this->request->param('id', 0)));
        if (!$dept) {
            return $this->error('部门不存在');
        }
        return $this->success('success', $dept);
    }

    public function add()
    {
        try {
            $id = $this->deptService->insert($this->request->param());
            return $this->toAjax($id > 0 ? 1 : 0);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function edit()
    {
        try {
            return $this->toAjax($this->deptService->update($this->request->param()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function remove($id = 0)
    {
        try {
            $deptId = (int) ($id ?: $this->request->param('id', $this->request->param('deptId', 0)));
            return $this->toAjax($this->deptService->delete($deptId));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function tree()
    {
        if (!Auth::hasPermi('system:dept:list') && !Auth::hasPermi('system:user:list') && !Auth::hasPermi('system:role:edit')) {
            return $this->error('没有权限，请联系管理员授权', 403);
        }
        return $this->success('success', $this->deptService->selectDeptTree());
    }
}
