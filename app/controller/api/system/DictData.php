<?php
declare(strict_types=1);

namespace app\controller\api\system;

use app\controller\BaseController;
use app\service\DictDataService;

/**
 * 字典数据 JSON API
 */
class DictData extends BaseController
{
    protected DictDataService $service;

    protected function initialize()
    {
        $this->service = new DictDataService();
    }

    public function list()
    {
        [$rows, $total] = $this->service->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    public function type($dictType = '')
    {
        $type = $dictType !== '' ? (string) $dictType : (string) $this->request->param('dictType', '');
        return $this->success('success', $this->service->selectByType($type));
    }

    public function get($id = 0)
    {
        $row = $this->service->get((int) ($id ?: $this->request->param('id', 0)));
        if (!$row) {
            return $this->error('字典数据不存在');
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
