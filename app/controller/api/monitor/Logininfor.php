<?php
declare(strict_types=1);

namespace app\controller\api\monitor;

use app\controller\BaseController;
use app\service\LogininforService;
use app\service\PasswordRetryService;

/**
 * 登录日志 JSON API
 */
class Logininfor extends BaseController
{
    protected LogininforService $service;

    protected function initialize()
    {
        $this->service = new LogininforService();
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

    public function unlock()
    {
        $loginName = (string) $this->request->param('loginName', '');
        if ($loginName === '') {
            return $this->error('请指定登录账号');
        }
        (new PasswordRetryService())->clear($loginName);
        return $this->success('已解锁');
    }
}
