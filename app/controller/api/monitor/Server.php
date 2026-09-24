<?php
declare(strict_types=1);

namespace app\controller\api\monitor;

use app\controller\BaseController;
use app\service\ServerService;

/**
 * 服务监控 JSON API
 */
class Server extends BaseController
{
    public function index()
    {
        return $this->success('success', (new ServerService())->collect());
    }
}
