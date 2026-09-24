<?php
declare(strict_types=1);

namespace app\controller\api\monitor;

use app\controller\BaseController;
use app\service\JobService;

/**
 * 定时任务 JSON API
 */
class Job extends BaseController
{
    protected JobService $service;

    protected function initialize()
    {
        $this->service = new JobService();
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
            return $this->error('任务不存在');
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

    public function changeStatus()
    {
        try {
            return $this->toAjax($this->service->changeStatus(
                (int) $this->request->param('jobId', 0),
                (string) $this->request->param('status', '1')
            ));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function run()
    {
        try {
            $this->service->runOnce((int) $this->request->param('jobId', 0));
            return $this->success('已执行');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
