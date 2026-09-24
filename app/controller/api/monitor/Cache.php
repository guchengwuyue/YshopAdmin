<?php
declare(strict_types=1);

namespace app\controller\api\monitor;

use app\controller\BaseController;
use app\service\CacheMonitorService;

/**
 * 缓存监控 JSON API
 */
class Cache extends BaseController
{
    protected CacheMonitorService $service;

    protected function initialize()
    {
        $this->service = new CacheMonitorService();
    }

    public function names()
    {
        return $this->success('success', $this->service->getCacheNames());
    }

    public function keys()
    {
        $name = (string) $this->request->param('cacheName', '');
        return $this->success('success', $this->service->getKeys($name));
    }

    public function value()
    {
        $name = (string) $this->request->param('cacheName', '');
        $key = (string) $this->request->param('cacheKey', '');
        $val = $this->service->getValue($name, $key);
        if (is_array($val) || is_object($val)) {
            $val = json_encode($val, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        return $this->success('success', [
            'cacheName' => $name,
            'cacheKey'  => $key,
            'cacheValue'=> (string) $val,
        ]);
    }

    public function clearName()
    {
        $this->service->clearCacheName((string) $this->request->param('cacheName', ''));
        return $this->success();
    }

    public function clearKey()
    {
        $this->service->clearCacheKey(
            (string) $this->request->param('cacheName', ''),
            (string) $this->request->param('cacheKey', '')
        );
        return $this->success();
    }

    public function clearAll()
    {
        $this->service->clearAll();
        return $this->success();
    }
}
