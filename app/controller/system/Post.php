<?php
declare(strict_types=1);

namespace app\controller\system;

use app\controller\BaseController;
use app\service\PostService;
use think\facade\View;

/**
 * 岗位管理
 */
class Post extends BaseController
{
    protected PostService $postService;

    protected function initialize()
    {
        $this->postService = new PostService();
    }

    /**
     * 岗位列表页
     */
    public function index()
    {
        return View::fetch('post/post');
    }

    /**
     * 查询岗位列表
     */
    public function list()
    {
        [$rows, $total] = $this->postService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 新增岗位
     */
    public function add()
    {
        if ($this->request->isPost()) {
            return $this->toAjax($this->postService->insert($this->request->post()) > 0 ? 1 : 0);
        }
        return View::fetch('post/add');
    }

    /**
     * 修改岗位
     * @param int $id 岗位 ID
     */
    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            return $this->toAjax($this->postService->update($this->request->post()));
        }
        return View::fetch('post/edit', ['post' => $this->postService->get((int) $id)]);
    }

    /**
     * 删除岗位
     */
    public function remove()
    {
        return $this->toAjax($this->postService->delete((string) $this->request->post('ids', '')));
    }

    /**
     * 导出岗位
     */
    public function export()
    {
        [$rows] = $this->postService->selectList($this->request->param(), true);
        $file = \app\common\ExcelUtil::export($rows, [
            'postId' => 'Post ID',
            'postCode' => 'Post Code',
            'postName' => 'Post Name',
            'postSort' => 'Sort',
            'status' => 'Status',
            'createTime' => 'Create Time',
        ], 'post');
        return $this->success($file);
    }
}
