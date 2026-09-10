<?php
declare(strict_types=1);

namespace app\controller\monitor;

use app\common\ExcelUtil;
use app\controller\BaseController;
use app\service\LogininforService;
use app\service\PasswordRetryService;
use think\facade\View;

/**
 * 登录日志
 */
class Logininfor extends BaseController
{
    protected LogininforService $logininforService;

    protected function initialize()
    {
        $this->logininforService = new LogininforService();
    }

    /**
     * 登录日志列表页
     */
    public function index()
    {
        return View::fetch('logininfor/logininfor');
    }

    /**
     * 查询登录日志列表
     */
    public function list()
    {
        [$rows, $total] = $this->logininforService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 删除登录日志
     */
    public function remove()
    {
        return $this->toAjax($this->logininforService->delete((string) $this->request->post('ids', '')));
    }

    /**
     * 清空登录日志
     */
    public function clean()
    {
        $this->logininforService->clean();
        return $this->success();
    }

    /**
     * 解锁账号（清除密码重试锁定）
     */
    public function unlock()
    {
        $loginName = (string) $this->request->post('loginName', '');
        if ($loginName === '') {
            return $this->error('loginName required');
        }
        (new PasswordRetryService())->clear($loginName);
        return $this->success('unlocked');
    }

    /**
     * 导出登录日志
     */
    public function export()
    {
        [$rows] = $this->logininforService->selectList($this->request->param(), true);
        $file = ExcelUtil::export($rows, [
            'infoId' => '访问编号',
            'loginName' => '登录名称',
            'ipaddr' => '登录地址',
            'loginLocation' => '登录地点',
            'browser' => '浏览器',
            'os' => '操作系统',
            'status' => '登录状态',
            'msg' => '操作信息',
            'loginTime' => '登录时间',
        ], 'logininfor');
        return $this->success($file);
    }
}
