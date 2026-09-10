<?php
declare(strict_types=1);

namespace app\controller\system;

use app\controller\BaseController;
use app\service\DeptService;
use think\facade\View;

/**
 * 部门管理
 */
class Dept extends BaseController
{
    protected DeptService $deptService;

    protected function initialize()
    {
        $this->deptService = new DeptService();
    }

    /**
     * 部门列表页
     */
    public function index()
    {
        return View::fetch('dept/dept');
    }

    /**
     * 查询部门列表（树表）
     */
    public function list()
    {
        $rows = $this->deptService->selectList($this->request->param());
        // treeTable(pagination=false) 需要原始 JSON 数组，而非 {rows:[]}
        return json($rows);
    }

    /**
     * 新增部门
     * @param int $parentId 上级部门 ID
     */
    public function add($parentId = 0)
    {
        if ($this->request->isPost()) {
            try {
                $id = $this->deptService->insert($this->request->post());
                return $this->toAjax($id > 0 ? 1 : 0);
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        $parentId = (int) ($this->request->param('parentId') ?: $parentId);
        $dept = $parentId ? $this->deptService->get($parentId) : ['deptId' => 0, 'deptName' => '主类目'];
        return View::fetch('dept/add', ['dept' => $dept]);
    }

    /**
     * 修改部门
     * @param int $id 部门 ID
     */
    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            try {
                return $this->toAjax($this->deptService->update($this->request->post()));
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        $dept = $this->deptService->get((int) $id);
        $parent = null;
        if ($dept && (int) ($dept['parentId'] ?? 0) > 0) {
            $parent = $this->deptService->get((int) $dept['parentId']);
        }
        return View::fetch('dept/edit', [
            'dept' => $dept,
            'parent' => $parent ?: ['deptId' => 0, 'deptName' => '主类目'],
        ]);
    }

    /**
     * 删除部门
     * @param int $id 部门 ID
     */
    public function remove($id = 0)
    {
        $deptId = (int) ($this->request->post('ids') ?: $id);
        try {
            return $this->toAjax($this->deptService->delete($deptId));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 部门树数据（ztree）
     * @param int $excludeId 排除的部门 ID（含其下级）
     */
    public function treeData($excludeId = 0)
    {
        return json($this->deptService->treeData(null, (int) $excludeId));
    }

    /**
     * 选择部门树
     */
    public function selectDeptTree($deptId = 0, $excludeId = 0)
    {
        $deptId = (int) $deptId;
        $excludeId = (int) $excludeId;
        $dept = $deptId > 0 ? $this->deptService->get($deptId) : null;
        if (!$dept) {
            $dept = ['deptId' => $deptId, 'deptName' => $deptId === 0 ? '主类目' : ''];
        }
        return View::fetch('dept/tree', [
            'dept'      => $dept,
            'deptId'    => $deptId,
            'excludeId' => $excludeId,
        ]);
    }
}
