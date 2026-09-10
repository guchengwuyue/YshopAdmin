<?php
declare(strict_types=1);

namespace app\controller\monitor;

use app\controller\BaseController;
use app\service\OnlineUserService;
use think\facade\View;

/**
 * 在线用户
 */
class Online extends BaseController
{
    protected OnlineUserService $service;

    protected function initialize()
    {
        $this->service = new OnlineUserService();
    }

    /**
     * 在线用户列表页
     */
    public function index()
    {
        return View::fetch('online/online');
    }

    /**
     * 查询在线用户列表
     */
    public function list()
    {
        [$rows, $total] = $this->service->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 批量强退
     */
    public function batchForceLogout()
    {
        $ids = (string) $this->request->post('ids', '');
        $result = $this->service->batchForceLogout($ids);
        if ($result['forced'] <= 0 && $result['skippedSelf']) {
            return $this->error('cannot force logout current session');
        }
        return $result['forced'] > 0 ? $this->success() : $this->error();
    }
}
