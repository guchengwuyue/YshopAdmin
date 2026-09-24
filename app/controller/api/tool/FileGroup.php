<?php
declare(strict_types=1);

namespace app\controller\api\tool;

use app\controller\BaseController;
use app\service\file\FileGroupService;

/**
 * 文件分组
 */
class FileGroup extends BaseController
{
    protected FileGroupService $service;

    protected function initialize()
    {
        $this->service = new FileGroupService();
    }

    public function tree()
    {
        return $this->success('success', $this->service->tree());
    }

    public function add()
    {
        try {
            $id = $this->service->insert($this->request->param());
            return $this->success('新增成功', ['id' => $id]);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function edit()
    {
        try {
            return $this->toAjax($this->service->update($this->request->param()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function remove()
    {
        try {
            $id = (int) $this->request->param('id', 0);
            return $this->toAjax($this->service->delete($id));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
