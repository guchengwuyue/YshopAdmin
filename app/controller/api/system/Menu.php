<?php
declare(strict_types=1);

namespace app\controller\api\system;

use app\controller\BaseController;
use app\service\MenuService;

/**
 * 菜单管理 JSON API
 */
class Menu extends BaseController
{
    protected MenuService $menuService;

    protected function initialize()
    {
        $this->menuService = new MenuService();
    }

    public function list()
    {
        $rows = $this->menuService->selectMenuList($this->request->param());
        return $this->success('success', $rows);
    }

    public function get($id = 0)
    {
        $menu = $this->menuService->get((int) ($id ?: $this->request->param('id', 0)));
        if (!$menu) {
            return $this->error('菜单不存在');
        }
        return $this->success('success', $menu);
    }

    public function add()
    {
        try {
            $id = $this->menuService->insert($this->request->param());
            return $this->toAjax($id > 0 ? 1 : 0);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function edit()
    {
        try {
            return $this->toAjax($this->menuService->update($this->request->param()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function remove($id = 0)
    {
        try {
            $menuId = (int) ($id ?: $this->request->param('id', $this->request->param('menuId', 0)));
            return $this->toAjax($this->menuService->delete($menuId));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function tree()
    {
        $excludeId = (int) $this->request->param('excludeId', 0);
        return $this->success('success', $this->menuService->menuTreeData($excludeId));
    }
}
