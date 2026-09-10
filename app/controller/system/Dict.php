<?php
declare(strict_types=1);

namespace app\controller\system;

use app\controller\BaseController;
use app\service\DictTypeService;
use think\facade\View;

/**
 * 字典类型管理
 */
class Dict extends BaseController
{
    protected DictTypeService $dictTypeService;

    protected function initialize()
    {
        $this->dictTypeService = new DictTypeService();
    }

    /**
     * 字典类型列表页
     */
    public function index()
    {
        return View::fetch('dict/type/type');
    }

    /**
     * 查询字典类型列表
     */
    public function list()
    {
        [$rows, $total] = $this->dictTypeService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 新增字典类型
     */
    public function add()
    {
        if ($this->request->isPost()) {
            try {
                return $this->toAjax($this->dictTypeService->insert($this->request->post()) > 0 ? 1 : 0);
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        return View::fetch('dict/type/add');
    }

    /**
     * 修改字典类型
     * @param int $id 字典类型 ID
     */
    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            try {
                return $this->toAjax($this->dictTypeService->update($this->request->post()));
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        return View::fetch('dict/type/edit', ['dict' => $this->dictTypeService->get((int) $id)]);
    }

    /**
     * 删除字典类型
     */
    public function remove()
    {
        return $this->toAjax($this->dictTypeService->delete((string) $this->request->post('ids', '')));
    }

    /**
     * 校验字典类型是否唯一
     */
    public function checkDictTypeUnique()
    {
        $ok = $this->dictTypeService->checkDictTypeUnique(
            (string) $this->request->param('dictType', ''),
            $this->request->param('dictId') ? (int) $this->request->param('dictId') : null
        );
        return $ok ? 'true' : 'false';
    }

    /**
     * 导出字典类型
     */
    public function export()
    {
        [$rows] = $this->dictTypeService->selectList($this->request->param(), true);
        $file = \app\common\ExcelUtil::export($rows, [
            'dictId' => 'Dict ID',
            'dictName' => 'Name',
            'dictType' => 'Type',
            'status' => 'Status',
            'createTime' => 'Create Time',
        ], 'dict');
        return $this->success($file);
    }
}
