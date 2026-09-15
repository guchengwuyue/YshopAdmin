<?php
declare(strict_types=1);

namespace app;

use app\common\Entry;
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
        // setRoot must run after Request is built from globals (HttpRun),
        // not in boot() — otherwise a later request instance drops the root.
        $this->app->event->listen('HttpRun', function () {
            if (!Entry::isAdmin()) {
                return;
            }
            $request = $this->app->request;
            $root = Entry::adminRoot();
            $request->setRoot($root);
            $path = ltrim((string) $request->pathinfo(), '/');
            if ($path === 'yshopadmin' || str_starts_with($path, 'yshopadmin/')) {
                $path = ltrim(substr($path, strlen('yshopadmin')), '/');
                $request->setPathinfo($path);
            }
        });

        AddonManager::instance()->boot();
    }
}
