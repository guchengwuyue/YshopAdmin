<?php
declare(strict_types=1);

namespace app\controller\api\system;

use app\controller\BaseController;
use app\service\NoticeService;

/**
 * 通知公告 JSON API
 */
class Notice extends BaseController
{
    protected NoticeService $noticeService;

    protected function initialize()
    {
        $this->noticeService = new NoticeService();
    }

    public function list()
    {
        [$rows, $total] = $this->noticeService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    public function get($id = 0)
    {
        $notice = $this->noticeService->get((int) ($id ?: $this->request->param('id', 0)));
        if (!$notice) {
            return $this->error('公告不存在');
        }
        return $this->success('success', $notice);
    }

    public function add()
    {
        try {
            $id = $this->noticeService->insert($this->request->param());
            return $this->toAjax($id > 0 ? 1 : 0);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function edit()
    {
        try {
            return $this->toAjax($this->noticeService->update($this->request->param()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function remove()
    {
        try {
            return $this->toAjax($this->noticeService->delete((string) $this->request->param('ids', '')));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
