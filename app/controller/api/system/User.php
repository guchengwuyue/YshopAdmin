<?php
declare(strict_types=1);

namespace app\controller\api\system;

use app\controller\BaseController;
use app\service\ConfigService;
use app\service\DeptService;
use app\service\DictDataService;
use app\service\PostService;
use app\service\RoleService;
use app\service\UserService;

/**
 * 用户管理 JSON API
 */
class User extends BaseController
{
    protected UserService $userService;

    protected function initialize()
    {
        $this->userService = new UserService();
    }

    public function list()
    {
        [$rows, $total] = $this->userService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    public function addForm()
    {
        return $this->success('success', $this->formPayload(null));
    }

    public function editForm($id = 0)
    {
        $userId = (int) ($id ?: $this->request->param('id', 0));
        $user = $this->userService->get($userId);
        if (!$user) {
            return $this->error('用户不存在');
        }
        return $this->success('success', $this->formPayload($user));
    }

    public function add()
    {
        try {
            $newId = $this->userService->insert($this->request->param());
            return $this->toAjax($newId > 0 ? 1 : 0);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function edit()
    {
        try {
            return $this->toAjax($this->userService->update($this->request->param()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function remove()
    {
        try {
            return $this->toAjax($this->userService->delete((string) $this->request->param('ids', '')));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function resetPwd()
    {
        try {
            $userId = (int) $this->request->param('userId', 0);
            $password = (string) $this->request->param('password', '');
            return $this->toAjax($this->userService->resetPwd($userId, $password));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function changeStatus()
    {
        try {
            $userId = (int) $this->request->param('userId', 0);
            $status = (string) $this->request->param('status', '0');
            return $this->toAjax($this->userService->changeStatus($userId, $status));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function deptTree()
    {
        return $this->success('success', (new DeptService())->selectDeptTree());
    }

    /**
     * @param array<string, mixed>|null $user
     * @return array<string, mixed>
     */
    protected function formPayload(?array $user): array
    {
        return [
            'user'         => $user,
            'roles'        => (new RoleService())->selectRolesAll(),
            'posts'        => (new PostService())->selectAll(),
            'initPassword' => (new ConfigService())->getKey('sys.user.initPassword', 'admin123'),
            'sexOptions'   => (new DictDataService())->selectByType('sys_user_sex'),
        ];
    }
}
