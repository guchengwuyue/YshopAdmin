<?php
declare(strict_types=1);

namespace app\controller\api\tool;

use app\service\addondev\MenuService;
use app\service\addondev\PackageService;
use app\service\addondev\ScaffoldService;
use app\controller\BaseController;

/**
 * 插件开发（系统工具，原 addondev）
 */
class AddonDev extends BaseController
{
    protected ScaffoldService $scaffold;
    protected PackageService $package;
    protected MenuService $menu;

    protected function initialize()
    {
        $this->scaffold = new ScaffoldService();
        $this->package = new PackageService();
        $this->menu = new MenuService();
    }

    public function list()
    {
        [$rows, $total] = $this->scaffold->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    public function get(string $name = '')
    {
        $name = $name !== '' ? $name : (string) $this->request->param('name', '');
        $info = get_addon_info($name);
        if (!$info) {
            return $this->error('插件不存在');
        }
        return $this->success('success', $info);
    }

    public function add()
    {
        try {
            return $this->toAjax($this->scaffold->create($this->request->param()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function edit()
    {
        try {
            $name = (string) $this->request->param('name', '');
            return $this->toAjax($this->scaffold->update($name, $this->request->param()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function package()
    {
        try {
            $name = (string) $this->request->param('name', '');
            $info = get_addon_info($name);
            if (!$info) {
                return $this->error('插件不存在');
            }
            $version = (string) $this->request->param('version', $info['version'] ?? '1.0.0');
            $path = $this->package->package($name, $version);
            return $this->success('打包成功', ['path' => $path]);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function menu()
    {
        [$rows, $total] = $this->menu->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    public function menuGet($id = 0)
    {
        $menuId = (int) ($id ?: $this->request->param('id', 0));
        $name = (string) $this->request->param('name', '');
        $menu = $this->menu->get($menuId, $name);
        if (!$menu) {
            return $this->error('菜单不存在或不属于该插件');
        }
        return $this->success('success', $menu);
    }

    public function menuTree()
    {
        $name = (string) $this->request->param('name', '');
        $excludeId = (int) $this->request->param('excludeId', 0);
        return $this->success('success', $this->menu->treeForSelect($name, $excludeId));
    }

    public function menuAdd()
    {
        try {
            $name = (string) $this->request->param('name', '');
            $id = $this->menu->insert($name, $this->request->param());
            return $this->toAjax($id > 0 ? 1 : 0);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function menuEdit()
    {
        try {
            $name = (string) $this->request->param('name', '');
            return $this->toAjax($this->menu->update($name, $this->request->param()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function menuRemove()
    {
        try {
            $name = (string) $this->request->param('name', '');
            $menuId = (int) $this->request->param('id', $this->request->param('menuId', 0));
            return $this->toAjax($this->menu->delete($name, $menuId));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function sync()
    {
        try {
            return $this->toAjax($this->menu->sync((string) $this->request->param('name', '')));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
