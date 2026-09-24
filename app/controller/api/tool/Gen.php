<?php
declare(strict_types=1);

namespace app\controller\api\tool;

use app\common\ExcelUtil;
use app\controller\BaseController;
use app\service\GenService;

/**
 * 代码生成 JSON API
 */
class Gen extends BaseController
{
    protected GenService $service;

    protected function initialize()
    {
        $this->service = new GenService();
    }

    public function list()
    {
        [$rows, $total] = $this->service->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    public function dbList()
    {
        [$rows, $total] = $this->service->selectDbTableList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    public function get($id = 0)
    {
        $row = $this->service->get((int) ($id ?: $this->request->param('id', 0)));
        if (!$row) {
            return $this->error('表不存在');
        }
        return $this->success('success', $row);
    }

    public function importTable()
    {
        try {
            $n = $this->service->importTable((string) $this->request->param('tables', ''));
            return $n > 0 ? $this->success() : $this->error('没有可导入的表');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function edit()
    {
        try {
            return $this->toAjax($this->service->update($this->request->param()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function remove()
    {
        return $this->toAjax($this->service->delete((string) $this->request->param('ids', '')));
    }

    public function preview($id = 0)
    {
        try {
            return $this->success('success', $this->service->preview((int) $id));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function download($id = '')
    {
        try {
            $tableIds = (string) ($id !== '' && $id !== null ? $id : $this->request->param('tables', ''));
            $file = $this->service->downloadZip($tableIds);
            return download(ExcelUtil::downloadDir() . $file, $file);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
