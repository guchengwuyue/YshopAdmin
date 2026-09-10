<?php
declare(strict_types=1);

namespace app\controller\monitor;

use app\common\ExcelUtil;
use app\controller\BaseController;
use app\service\JobLogService;
use app\service\JobService;
use think\facade\View;

/**
 * 调度日志
 */
class JobLog extends BaseController
{
    protected JobLogService $service;

    protected function initialize()
    {
        $this->service = new JobLogService();
    }

    /**
     * 调度日志列表页
     */
    public function index()
    {
        $jobId = (int) $this->request->param('jobId', 0);
        $job = $jobId ? (new JobService())->get($jobId) : null;
        return View::fetch('job/jobLog', ['job' => $job, 'jobId' => $jobId]);
    }

    /**
     * 查询调度日志列表
     */
    public function list()
    {
        [$rows, $total] = $this->service->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 调度日志详情
     * @param int $id 日志 ID
     */
    public function detail($id = 0)
    {
        return View::fetch('job/detail', ['job' => $this->service->get((int) $id)]);
    }

    /**
     * 删除调度日志
     */
    public function remove()
    {
        return $this->toAjax($this->service->delete((string) $this->request->post('ids', '')));
    }

    /**
     * 清空调度日志
     */
    public function clean()
    {
        $this->service->clean();
        return $this->success();
    }

    /**
     * 导出调度日志
     */
    public function export()
    {
        [$rows] = $this->service->selectList($this->request->param(), true);
        $file = ExcelUtil::export($rows, [
            'jobLogId' => 'Log ID',
            'jobName' => 'Job Name',
            'jobGroup' => 'Job Group',
            'invokeTarget' => 'Invoke Target',
            'jobMessage' => 'Message',
            'status' => 'Status',
            'createTime' => 'Create Time',
        ], 'jobLog');
        return $this->success($file);
    }
}
