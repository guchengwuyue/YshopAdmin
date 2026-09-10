<?php
declare(strict_types=1);

namespace app\controller;

use app\common\Auth;
use app\service\LoginService;
use think\facade\View;

/**
 * 登录与退出
 */
class Login extends BaseController
{
    /**
     * 登录页
     */
    public function index()
    {
        if (Auth::getLoginUser()) {
            return redirect((string) url('/index'));
        }
        return View::fetch('index', [
            'captchaEnabled' => true,
            'captchaType'    => 'math',
            'isRemembered'   => false,
            'isAllowRegister'=> false,
        ]);
    }

    /**
     * Ajax 登录
     */
    public function ajaxLogin()
    {
        $username = (string) $this->request->post('username', '');
        $password = (string) $this->request->post('password', '');
        $validateCode = $this->request->post('validateCode');
        $service = new LoginService();
        $result = $service->login($username, $password, $validateCode !== null ? (string) $validateCode : null);
        if ($result['ok']) {
            return $this->success($result['msg']);
        }
        return $this->error($result['msg']);
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        (new LoginService())->logout();
        return redirect((string) url('/login'));
    }

    /**
     * 未授权提示页
     */
    public function unauth()
    {
        return View::fetch('unauth');
    }
}
