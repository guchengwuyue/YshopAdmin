<?php
declare(strict_types=1);

namespace app\controller\monitor;

use app\controller\BaseController;
use app\service\CacheMonitorService;
use think\facade\View;

/**
 * 缓存监控
 */
class Cache extends BaseController
{
    protected CacheMonitorService $service;

    protected function initialize()
    {
        $this->service = new CacheMonitorService();
    }

    /**
     * 缓存监控页
     */
    public function index()
    {
        return View::fetch('cache/cache', [
            'cacheNames' => $this->service->getCacheNames(),
        ]);
    }

    /**
     * 获取缓存名称列表（HTML 片段）
     */
    public function getNames()
    {
        $names = $this->service->getCacheNames();
        $html = '';
        foreach ($names as $i => $name) {
            $n = htmlspecialchars($name, ENT_QUOTES);
            $html .= '<tr><td>' . ($i + 1) . '</td>'
                . '<td style="word-wrap:break-word;word-break:break-all;" onclick="getCacheKeys(\'' . $n . '\')">' . $n . '</td>'
                . '<td style="width:50px"><a href="javascript:;" onclick="clearCacheName(\'' . $n . '\')" title="清空"><i class="fa fa-trash-o text-danger"></i></a></td></tr>';
        }
        return response($html);
    }

    /**
     * 获取指定缓存下的键列表（HTML 片段）
     */
    public function getKeys()
    {
        $cacheName = (string) $this->request->post('cacheName', '');
        $keys = $this->service->getKeys($cacheName);
        $html = '';
        $cn = htmlspecialchars($cacheName, ENT_QUOTES);
        foreach ($keys as $i => $key) {
            $k = htmlspecialchars($key, ENT_QUOTES);
            $html .= '<tr><td>' . ($i + 1) . '</td>'
                . '<td style="word-wrap:break-word;word-break:break-all;" onclick="getCacheValue(\'' . $cn . '\',\'' . $k . '\')">' . $k . '</td>'
                . '<td style="width:50px"><a href="javascript:;" onclick="clearCacheKey(\'' . $cn . '\',\'' . $k . '\')" title="清空"><i class="fa fa-trash-o text-danger"></i></a></td></tr>';
        }
        return response($html);
    }

    /**
     * 获取缓存键值内容（HTML 片段）
     */
    public function getValue()
    {
        $cacheName = (string) $this->request->post('cacheName', '');
        $cacheKey = (string) $this->request->post('cacheKey', '');
        $val = $this->service->getValue($cacheName, $cacheKey);
        if (is_array($val) || is_object($val)) {
            $val = json_encode($val, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        $cn = htmlspecialchars($cacheName, ENT_QUOTES);
        $ck = htmlspecialchars($cacheKey, ENT_QUOTES);
        $cv = htmlspecialchars((string) $val, ENT_QUOTES);
        $html = '<div class="col-sm-12">'
            . '<div class="form-group"><label>缓存名称：</label><input type="text" class="form-control" value="' . $cn . '"></div>'
            . '<div class="form-group"><label>缓存键名：</label><input type="text" class="form-control" value="' . $ck . '"></div>'
            . '<div class="form-group"><label>缓存内容：</label><textarea class="form-control" style="height:100px">' . $cv . '</textarea></div>'
            . '</div>';
        return response($html);
    }

    /**
     * 清空指定缓存名称下全部键
     */
    public function clearCacheName()
    {
        $this->service->clearCacheName((string) $this->request->post('cacheName', ''));
        return $this->success();
    }

    /**
     * 清空指定缓存键
     */
    public function clearCacheKey()
    {
        $this->service->clearCacheKey(
            (string) $this->request->post('cacheName', ''),
            (string) $this->request->post('cacheKey', '')
        );
        return $this->success();
    }

    /**
     * 清空全部缓存
     */
    public function clearAll()
    {
        $this->service->clearAll();
        return $this->success();
    }
}
