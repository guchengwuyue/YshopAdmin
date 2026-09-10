<?php
declare(strict_types=1);

namespace app\controller\monitor;

use app\controller\BaseController;
use app\service\ServerService;
use think\facade\View;

/**
 * 服务监控
 */
class Server extends BaseController
{
    /**
     * 服务监控页
     */
    public function index()
    {
        $server = (new ServerService())->collect();
        return View::fetch('server/server', ['server' => $server]);
    }
}
