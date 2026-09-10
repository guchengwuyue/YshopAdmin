<?php
declare(strict_types=1);

namespace app\controller\monitor;

use app\common\ExcelUtil;
use app\controller\BaseController;
use app\service\DictDataService;
use app\service\JobService;
use think\facade\View;

/**
 * 定时任务
 */
class Job extends BaseController
{
    protected JobService $service;

    protected function initialize()
    {
        $this->service = new JobService();
    }

    /**
     * 定时任务列表页
     */
    public function index()
    {
        $dict = new DictDataService();
        return View::fetch('job/job', [
            'jobGroupDict'  => $dict->selectByType('sys_job_group'),
            'jobStatusDict' => $dict->selectByType('sys_job_status'),
        ]);
    }

    /**
     * 查询定时任务列表
     */
    public function list()
    {
        [$rows, $total] = $this->service->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 新增定时任务
     */
    public function add()
    {
        if ($this->request->isPost()) {
            try {
                return $this->toAjax($this->service->insert($this->request->post()) > 0 ? 1 : 0);
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        $dict = new DictDataService();
        return View::fetch('job/add', [
            'jobGroupDict' => $dict->selectByType('sys_job_group'),
        ]);
    }

    /**
     * 修改定时任务
     * @param int $id 任务 ID
     */
    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            try {
                return $this->toAjax($this->service->update($this->request->post()));
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        $dict = new DictDataService();
        return View::fetch('job/edit', [
            'job' => $this->service->get((int) $id),
            'jobGroupDict' => $dict->selectByType('sys_job_group'),
        ]);
    }

    /**
     * 删除定时任务
     */
    public function remove()
    {
        return $this->toAjax($this->service->delete((string) $this->request->post('ids', '')));
    }

    /**
     * 定时任务详情
     * @param int $id 任务 ID
     */
    public function detail($id = 0)
    {
        return View::fetch('job/detail', ['job' => $this->service->get((int) $id)]);
    }

    /**
     * 修改任务状态
     */
    public function changeStatus()
    {
        try {
            return $this->toAjax($this->service->changeStatus(
                (int) $this->request->post('jobId'),
                (string) $this->request->post('status')
            ));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 立即执行一次
     */
    public function run()
    {
        try {
            $this->service->runOnce((int) $this->request->post('jobId'));
            return $this->success();
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 校验 Cron 表达式是否合法
     */
    public function checkCronExpressionIsValid()
    {
        $ok = $this->service->checkCronExpression((string) $this->request->post('cronExpression', ''));
        return $ok ? 'true' : 'false';
    }

    /**
     * Cron 表达式生成器页
     */
    public function cron()
    {
        return View::fetch('job/cron');
    }

    /**
     * 导出定时任务
     */
    public function export()
    {
        [$rows] = $this->service->selectList($this->request->param(), true);
        $file = ExcelUtil::export($rows, [
            'jobId' => 'Job ID',
            'jobName' => 'Job Name',
            'jobGroup' => 'Job Group',
            'invokeTarget' => 'Invoke Target',
            'cronExpression' => 'Cron',
            'status' => 'Status',
            'createTime' => 'Create Time',
        ], 'job');
        return $this->success($file);
    }
}
