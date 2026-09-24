<?php
declare(strict_types=1);

namespace app\controller\api\tool;

use app\service\file\FileService;
use app\controller\BaseController;

/**
 * 文件列表（系统工具）
 */
class File extends BaseController
{
    protected FileService $service;

    protected function initialize()
    {
        $this->service = new FileService();
    }

    public function list()
    {
        [$rows, $total] = $this->service->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    public function upload()
    {
        try {
            $file = $this->request->file('file');
            if (!$file) {
                return $this->error('请选择文件');
            }
            $groupId = (int) $this->request->param('groupId', $this->request->param('group_id', 0));
            return $this->success('上传成功', $this->service->uploadByMaster($file, '', $groupId));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function remove()
    {
        try {
            return $this->toAjax($this->service->delete((string) $this->request->param('ids', '')));
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, '记录已删除')) {
                return $this->success($msg);
            }
            return $this->error($msg);
        }
    }

    public function move()
    {
        try {
            $ids = (string) $this->request->param('ids', '');
            $groupId = (int) $this->request->param('groupId', $this->request->param('group_id', 0));
            return $this->toAjax($this->service->move($ids, $groupId));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
