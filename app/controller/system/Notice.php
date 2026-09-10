<?php
declare(strict_types=1);

namespace app\controller\system;

use app\controller\BaseController;
use app\service\DictDataService;
use app\service\NoticeService;
use think\facade\View;

/**
 * 通知公告管理
 */
class Notice extends BaseController
{
    protected NoticeService $noticeService;

    protected function initialize()
    {
        $this->noticeService = new NoticeService();
    }

    /**
     * 通知公告列表页
     */
    public function index()
    {
        return View::fetch('notice/notice', [
            'noticeTypes' => (new DictDataService())->selectByType('sys_notice_type'),
        ]);
    }

    /**
     * 查询通知公告列表
     */
    public function list()
    {
        [$rows, $total] = $this->noticeService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 新增通知公告
     */
    public function add()
    {
        if ($this->request->isPost()) {
            return $this->toAjax($this->noticeService->insert($this->request->post()) > 0 ? 1 : 0);
        }
        return View::fetch('notice/add', [
            'noticeTypes' => (new DictDataService())->selectByType('sys_notice_type'),
        ]);
    }

    /**
     * 修改通知公告
     * @param int $id 公告 ID
     */
    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            return $this->toAjax($this->noticeService->update($this->request->post()));
        }
        return View::fetch('notice/edit', [
            'notice' => $this->noticeService->get((int) $id),
            'noticeTypes' => (new DictDataService())->selectByType('sys_notice_type'),
        ]);
    }

    /**
     * 删除通知公告
     */
    public function remove()
    {
        return $this->toAjax($this->noticeService->delete((string) $this->request->post('ids', '')));
    }
}
