<?php
declare(strict_types=1);

namespace app\controller\api\system;

use app\controller\BaseController;
use app\service\DictTypeService;

/**
 * 字典类型 JSON API
 */
class Dict extends BaseController
{
    protected DictTypeService $service;

    protected function initialize()
    {
        $this->service = new DictTypeService();
    }

    public function list()
    {
        [$rows, $total] = $this->service->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    public function get($id = 0)
    {
        $row = $this->service->get((int) ($id ?: $this->request->param('id', 0)));
        if (!$row) {
            return $this->error('字典类型不存在');
        }
        return $this->success('success', $row);
    }

    public function add()
    {
        try {
            $id = $this->service->insert($this->request->param());
            return $this->toAjax($id > 0 ? 1 : 0);
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
        try {
            return $this->toAjax($this->service->delete((string) $this->request->param('ids', '')));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
