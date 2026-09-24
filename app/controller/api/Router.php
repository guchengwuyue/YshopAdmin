<?php
declare(strict_types=1);

namespace app\controller\api;

use app\controller\BaseController;
use app\service\MenuService;

/**
 * 管理端动态路由
 */
class Router extends BaseController
{
    public function getRouters()
    {
        $routes = (new MenuService())->selectRouters($this->getUserId());
        return $this->success('success', $routes);
    }
}
