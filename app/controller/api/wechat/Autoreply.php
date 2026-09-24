<?php
declare(strict_types=1);

namespace app\controller\api\wechat;

use app\service\wechat\AutoreplyService;
use app\controller\BaseController;

/**
 * 自动回复 JSON API
 */
class Autoreply extends BaseController
{
    protected AutoreplyService $service;

    protected function initialize()
    {
        $this->service = new AutoreplyService();
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
            return $this->error('记录不存在');
        }
        return $this->success('success', $row);
    }

    public function add()
    {
        try {
            return $this->toAjax($this->service->insert($this->request->param()) > 0 ? 1 : 0);
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
