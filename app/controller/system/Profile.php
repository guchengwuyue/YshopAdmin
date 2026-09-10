<?php
declare(strict_types=1);

namespace app\controller\system;

use app\controller\BaseController;
use app\service\UserService;
use think\facade\View;

/**
 * 个人中心
 */
class Profile extends BaseController
{
    /**
     * 个人信息页
     */
    public function index()
    {
        $user = (new UserService())->get($this->getUserId());
        return View::fetch('user/profile/profile', ['user' => $user]);
    }

    /**
     * 更新个人信息
     */
    public function update()
    {
        try {
            return $this->toAjax((new UserService())->updateProfile($this->request->post()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 修改个人密码
     */
    public function resetPwd()
    {
        if ($this->request->isPost()) {
            try {
                return $this->toAjax((new UserService())->updatePwd(
                    (string) $this->request->post('oldPassword', ''),
                    (string) $this->request->post('newPassword', '')
                ));
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        return View::fetch('user/profile/resetPwd');
    }
}
