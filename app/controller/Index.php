<?php
declare(strict_types=1);

namespace app\controller;

use app\common\Auth;
use app\service\ConfigService;
use app\service\MenuService;
use think\facade\View;

/**
 * 后台首页
 */
class Index extends BaseController
{
    /**
     * 主框架页（含侧栏菜单）
     */
    public function index()
    {
        $user = Auth::getLoginUser();
        if (!$user) {
            return redirect((string) url('/login'));
        }
        $menus = (new MenuService())->selectMenusByUserId(Auth::getUserId());
        $configService = new ConfigService();
        $sideTitle = $configService->getKey('sys.index.sideTitle', '意象管理系统');
        $demoEnabled = $configService->getKey('sys.demo.enabled', 'false') === 'true';
        return View::fetch('index', [
            'menus'   => $menus,
            'user'    => [
                'loginName' => $user['login_name'] ?? $user['loginName'] ?? '',
                'userName'  => $user['user_name'] ?? $user['userName'] ?? '',
                'avatar'    => $user['avatar'] ?? '',
            ],
            'sideTitle'   => $sideTitle,
            'demoEnabled' => $demoEnabled,
            'isMobile'    => false,
        ]);

    }

    /**
     * 首页内容区
     */
    public function main()
    {
        return View::fetch('main', ['version' => '1.0.0-php']);
    }

    /**
     * 根路径跳转（已登录进后台，否则去登录）
     */
    public function home()
    {
        if (Auth::getLoginUser()) {
            return redirect((string) url('/index'));
        }
        return redirect((string) url('/login'));
    }
}
