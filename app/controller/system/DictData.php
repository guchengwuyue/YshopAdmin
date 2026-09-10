<?php
declare(strict_types=1);

namespace app\controller\system;

use app\controller\BaseController;
use app\service\DictDataService;
use app\service\DictTypeService;
use think\facade\View;

/**
 * 字典数据管理
 */
class DictData extends BaseController
{
    protected DictDataService $dictDataService;

    protected function initialize()
    {
        $this->dictDataService = new DictDataService();
    }

    /**
     * 字典数据列表页
     */
    public function index()
    {
        $dictType = (string) $this->request->param('dictType', '');
        $dictId = (int) $this->request->param('dictId', 0);
        if ($dictId && $dictType === '') {
            $dict = (new DictTypeService())->get($dictId);
            $dictType = $dict['dictType'] ?? '';
        }
        return View::fetch('dict/data/data', [
            'dictType' => $dictType,
            'dictId'   => $dictId,
            'dictList' => (new DictTypeService())->selectAll(),
        ]);
    }

    /**
     * 查询字典数据列表
     */
    public function list()
    {
        [$rows, $total] = $this->dictDataService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 新增字典数据
     */
    public function add()
    {
        if ($this->request->isPost()) {
            return $this->toAjax($this->dictDataService->insert($this->request->post()) > 0 ? 1 : 0);
        }
        $dictType = (string) $this->request->param('dictType', '');
        return View::fetch('dict/data/add', ['dictType' => $dictType]);
    }

    /**
     * 修改字典数据
     * @param int $id 字典数据 ID
     */
    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            return $this->toAjax($this->dictDataService->update($this->request->post()));
        }
        return View::fetch('dict/data/edit', ['dict' => $this->dictDataService->get((int) $id)]);
    }

    /**
     * 删除字典数据
     */
    public function remove()
    {
        return $this->toAjax($this->dictDataService->delete((string) $this->request->post('ids', '')));
    }

    /**
     * 导出字典数据
     */
    public function export()
    {
        [$rows] = $this->dictDataService->selectList($this->request->param(), true);
        $file = \app\common\ExcelUtil::export($rows, [
            'dictCode' => 'Dict Code',
            'dictSort' => 'Sort',
            'dictLabel' => 'Label',
            'dictValue' => 'Value',
            'dictType' => 'Type',
            'status' => 'Status',
        ], 'dictData');
        return $this->success($file);
    }
}
