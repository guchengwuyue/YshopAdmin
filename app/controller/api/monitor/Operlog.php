<?php
declare(strict_types=1);

namespace app\controller\api\monitor;

use app\controller\BaseController;
use app\service\OperLogService;

/**
 * 操作日志 JSON API
 */
class Operlog extends BaseController
{
    protected OperLogService $service;

    protected function initialize()
    {
        $this->service = new OperLogService();
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
            return $this->error('日志不存在');
        }
        return $this->success('success', $row);
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
