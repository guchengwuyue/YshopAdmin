<?php
declare(strict_types=1);

namespace app;

use app\common\addon\AddonManager;
use think\Service;

/**
 * 应用服务类
 */
class AppService extends Service
{
    public function register()
    {
        // 服务注册
    }

    public function boot()
    {
        AddonManager::instance()->boot();
    }
}
