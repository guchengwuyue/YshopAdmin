<?php
declare(strict_types=1);

namespace app\controller\api\monitor;

use app\controller\BaseController;
use app\service\JobLogService;

/**
 * 调度日志 JSON API
 */
class JobLog extends BaseController
{
    protected JobLogService $service;

    protected function initialize()
    {
        $this->service = new JobLogService();
    }

    public function list()
    {
        [$rows, $total] = $this->service->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    public function remove()
    {
        return $this->toAjax($this->service->delete((string) $this->request->param('ids', '')));
    }

    public function clean()
    {
        $this->service->clean();
        return $this->success();
    }
}
