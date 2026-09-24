<?php
declare(strict_types=1);

namespace app\controller\api\system;

use app\controller\BaseController;
use app\service\PostService;

/**
 * 岗位管理 JSON API
 */
class Post extends BaseController
{
    protected PostService $postService;

    protected function initialize()
    {
        $this->postService = new PostService();
    }

    public function list()
    {
        [$rows, $total] = $this->postService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    public function get($id = 0)
    {
        $post = $this->postService->get((int) ($id ?: $this->request->param('id', 0)));
        if (!$post) {
            return $this->error('岗位不存在');
        }
        return $this->success('success', $post);
    }

    public function add()
    {
        try {
            $id = $this->postService->insert($this->request->param());
            return $this->toAjax($id > 0 ? 1 : 0);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function edit()
    {
        try {
            return $this->toAjax($this->postService->update($this->request->param()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function remove()
    {
        try {
            return $this->toAjax($this->postService->delete((string) $this->request->param('ids', '')));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
