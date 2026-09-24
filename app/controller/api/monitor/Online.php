<?php
declare(strict_types=1);

namespace app\controller\api\monitor;

use app\controller\BaseController;
use app\service\OnlineUserService;

/**
 * 在线用户 JSON API
 */
class Online extends BaseController
{
    protected OnlineUserService $service;

    protected function initialize()
    {
        $this->service = new OnlineUserService();
    }

    public function list()
    {
        [$rows, $total] = $this->service->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    public function batchForceLogout()
    {
        try {
            $result = $this->service->batchForceLogout((string) $this->request->param('ids', ''));
            return $this->success('强退完成', $result);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
