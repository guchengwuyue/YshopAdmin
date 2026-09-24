<?php
declare(strict_types=1);

namespace app\controller\api\wechat;

use app\service\wechat\MenuService;
use app\controller\BaseController;

/**
 * 微信菜单 JSON API
 */
class Menu extends BaseController
{
    protected MenuService $service;

    protected function initialize()
    {
        $this->service = new MenuService();
    }

    public function index()
    {
        return $this->success('success', $this->service->getEditorData());
    }

    public function save()
    {
        try {
            $menu = $this->request->param('menu', []);
            if (is_string($menu)) {
                $menu = json_decode($menu, true) ?: [];
            }
            if (!is_array($menu)) {
                return $this->error('菜单数据无效');
            }
            $this->service->save($menu);
            return $this->success('保存成功');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function pull()
    {
        try {
            $this->service->pullRemote();
            return $this->success('已从微信拉取');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function sync()
    {
        try {
            $this->service->pushSync();
            return $this->success('已同步到微信');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
