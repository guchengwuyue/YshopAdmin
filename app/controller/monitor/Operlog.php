<?php
declare(strict_types=1);

namespace app\controller\monitor;

use app\controller\BaseController;
use app\service\OperLogService;
use think\facade\View;

/**
 * 操作日志
 */
class Operlog extends BaseController
{
    protected OperLogService $operLogService;

    protected function initialize()
    {
        $this->operLogService = new OperLogService();
    }

    /**
     * 操作日志列表页
     */
    public function index()
    {
        return View::fetch('operlog/operlog');
    }

    /**
     * 查询操作日志列表
     */
    public function list()
    {
        [$rows, $total] = $this->operLogService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 操作日志详情
     * @param int $id 日志 ID
     */
    public function detail($id = 0)
    {
        return View::fetch('operlog/detail', [
            'operLog' => $this->operLogService->get((int) $id),
        ]);
    }

    /**
     * 删除操作日志
     */
    public function remove()
    {
        return $this->toAjax($this->operLogService->delete((string) $this->request->post('ids', '')));
    }

    /**
     * 清空操作日志
     */
    public function clean()
    {
        $this->operLogService->clean();
        return $this->success();
    }

    /**
     * 导出操作日志
     */
    public function export()
    {
        [$rows] = $this->operLogService->selectList($this->request->param(), true);
        $file = \app\common\ExcelUtil::export($rows, [
            'operId' => 'Oper ID',
            'title' => 'Title',
            'businessType' => 'Business Type',
            'operName' => 'Oper Name',
            'operIp' => 'IP',
            'status' => 'Status',
            'operTime' => 'Oper Time',
        ], 'operlog');
        return $this->success($file);
    }
}
