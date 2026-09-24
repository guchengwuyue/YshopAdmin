<?php
declare(strict_types=1);

namespace app\controller\api\tool;

use app\service\file\ConfigService;
use app\controller\BaseController;

/**
 * 文件存储配置（系统工具）
 */
class FileConfig extends BaseController
{
    protected ConfigService $service;

    protected function initialize()
    {
        $this->service = new ConfigService();
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
            return $this->error('配置不存在');
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

    public function master()
    {
        try {
            return $this->toAjax($this->service->setMaster((int) $this->request->param('id', 0)));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function test()
    {
        try {
            $this->service->testConnection(null, (int) $this->request->param('id', 0));
            return $this->success('连通测试成功');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
