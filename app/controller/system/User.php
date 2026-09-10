<?php
declare(strict_types=1);

namespace app\controller\system;

use app\controller\BaseController;
use app\service\ConfigService;
use app\service\DictDataService;
use app\service\PostService;
use app\service\RoleService;
use app\service\UserService;
use think\facade\View;

/**
 * 用户管理
 */
class User extends BaseController
{
    protected UserService $userService;

    protected function initialize()
    {
        $this->userService = new UserService();
    }

    /**
     * 用户列表页
     */
    public function index()
    {
        return View::fetch('user/user');
    }

    /**
     * 查询用户列表
     */
    public function list()
    {
        [$rows, $total] = $this->userService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 新增用户
     */
    public function add()
    {
        if ($this->request->isPost()) {
            try {
                $id = $this->userService->insert($this->request->post());
                return $this->toAjax($id > 0 ? 1 : 0);
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        $roles = (new RoleService())->selectRolesAll();
        $posts = (new PostService())->selectAll();
        $initPassword = (new ConfigService())->getKey('sys.user.initPassword', 'admin123');
        $sexDict = (new DictDataService())->selectByType('sys_user_sex');
        return View::fetch('user/add', [
            'roles' => $roles,
            'posts' => $posts,
            'initPassword' => $initPassword,
            'sexDict' => $sexDict,
        ]);
    }

    /**
     * 修改用户
     * @param int $id 用户 ID
     */
    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            try {
                return $this->toAjax($this->userService->update($this->request->post()));
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        $user = $this->userService->get((int) $id);
        $roles = (new RoleService())->selectRolesAll();
        $posts = (new PostService())->selectAll();
        $sexDict = (new DictDataService())->selectByType('sys_user_sex');
        return View::fetch('user/edit', [
            'user'  => $user,
            'roles' => $roles,
            'posts' => $posts,
            'sexDict' => $sexDict,
            'roleIds' => $user['roleIds'] ?? [],
            'postIds' => $user['postIds'] ?? [],
        ]);
    }

    /**
     * 删除用户
     */
    public function remove()
    {
        $ids = (string) $this->request->post('ids', '');
        try {
            return $this->toAjax($this->userService->delete($ids));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 重置密码
     * @param int $id 用户 ID
     */
    public function resetPwd($id = 0)
    {
        if ($this->request->isPost()) {
            $userId = (int) $this->request->post('userId', 0);
            $password = (string) $this->request->post('password', '');
            try {
                return $this->toAjax($this->userService->resetPwd($userId, $password));
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        $user = $this->userService->get((int) $id);
        return View::fetch('user/resetPwd', ['user' => $user]);
    }

    /**
     * 校验登录账号是否唯一
     */
    public function checkLoginNameUnique()
    {
        $loginName = (string) $this->request->param('loginName', '');
        $userId = $this->request->param('userId');
        $ok = $this->userService->checkLoginNameUnique($loginName, $userId ? (int) $userId : null);
        return $ok ? 'true' : 'false';
    }

    /**
     * 修改用户状态
     */
    public function changeStatus()
    {
        try {
            return $this->toAjax($this->userService->changeStatus(
                (int) $this->request->post('userId'),
                (string) $this->request->post('status')
            ));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 部门树数据（用户选部门）
     */
    public function deptTreeData()
    {
        $nodes = (new \app\service\DeptService())->treeData();
        return json($nodes);
    }

    /**
     * 选择部门树页面
     * @param int $deptId 当前部门 ID
     */
    public function selectDeptTree($deptId = 0)
    {
        $deptService = new \app\service\DeptService();
        $dept = $deptService->get((int) $deptId);
        if (!$dept) {
            $dept = ['deptId' => '', 'deptName' => ''];
        }
        return View::fetch('user/deptTree', ['dept' => $dept]);
    }

    /**
     * 导出用户
     */
    public function export()
    {
        [$rows] = $this->userService->selectList($this->request->param(), true);
        $file = \app\common\ExcelUtil::export($rows, [
            'userId' => 'User ID',
            'loginName' => 'Login Name',
            'userName' => 'User Name',
            'email' => 'Email',
            'phonenumber' => 'Phone',
            'status' => 'Status',
            'createTime' => 'Create Time',
        ], 'user');
        return $this->success($file);
    }

    /**
     * 下载用户导入模板
     */
    public function importTemplate()
    {
        $file = \app\common\ExcelUtil::template([
            'loginName' => 'Login Name',
            'userName' => 'User Name',
            'email' => 'Email',
            'phonenumber' => 'Phone',
            'sex' => 'Sex',
            'status' => 'Status',
            'deptId' => 'Dept ID',
        ], 'user');
        return $this->success($file);
    }

    /**
     * 导入用户数据
     */
    public function importData()
    {
        $file = $this->request->file('file');
        if (!$file) {
            return $this->error('file required');
        }
        $path = $file->getRealPath() ?: $file->getPathname();
        $updateSupport = filter_var($this->request->post('updateSupport', false), FILTER_VALIDATE_BOOLEAN);
        try {
            $rows = \app\common\ExcelUtil::import($path, [
                'loginName' => 'Login Name',
                'userName' => 'User Name',
                'email' => 'Email',
                'phonenumber' => 'Phone',
                'sex' => 'Sex',
                'status' => 'Status',
                'deptId' => 'Dept ID',
            ]);
            $msg = $this->userService->importRows($rows, $updateSupport);
            return $this->success($msg);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
