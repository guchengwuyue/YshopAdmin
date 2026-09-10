<?php
declare(strict_types=1);

namespace app\controller\system;

use app\controller\BaseController;
use app\service\MenuService;
use think\facade\View;

/**
 * 菜单管理
 */
class Menu extends BaseController
{
    protected MenuService $menuService;

    protected function initialize()
    {
        $this->menuService = new MenuService();
    }

    /**
     * 菜单列表页
     */
    public function index()
    {
        return View::fetch('menu/menu');
    }

    /**
     * 查询菜单列表（树表，无分页）
     */
    public function list()
    {
        $rows = $this->menuService->selectMenuList($this->request->param());
        // treeTable(pagination=false) 需要原始 JSON 数组，而非 {rows:[]}
        return json($rows);
    }

    /**
     * 新增菜单
     * @param int $parentId 上级菜单 ID
     */
    public function add($parentId = 0)
    {
        if ($this->request->isPost()) {
            try {
                $id = $this->menuService->insert($this->request->post());
                return $this->toAjax($id > 0 ? 1 : 0);
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        $parentId = (int) ($this->request->param('parentId') ?: $parentId);
        $menu = $parentId ? $this->menuService->get($parentId) : ['menuId' => 0, 'menuName' => '主目录'];
        return View::fetch('menu/add', ['menu' => $menu]);
    }

    /**
     * 修改菜单
     * @param int $id 菜单 ID
     */
    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            try {
                return $this->toAjax($this->menuService->update($this->request->post()));
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        $menu = $this->menuService->get((int) $id);
        $parent = null;
        if ($menu && (int) ($menu['parentId'] ?? 0) > 0) {
            $parent = $this->menuService->get((int) $menu['parentId']);
        }
        return View::fetch('menu/edit', [
            'menu' => $menu,
            'parent' => $parent ?: ['menuId' => 0, 'menuName' => '主目录'],
        ]);
    }

    /**
     * 删除菜单
     * @param int $id 菜单 ID
     */
    public function remove($id = 0)
    {
        $menuId = (int) ($this->request->post('ids') ?: $id);
        try {
            return $this->toAjax($this->menuService->delete($menuId));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 菜单树数据（ztree）
     * @param int $excludeId 排除的菜单 ID（含其下级）
     */
    public function treeData($excludeId = 0)
    {
        return json($this->menuService->menuTreeData((int) $excludeId));
    }

    /**
     * 选择上级菜单树
     */
    public function selectMenuTree($menuId = 0, $excludeId = 0)
    {
        $menuId = (int) $menuId;
        $excludeId = (int) $excludeId;
        $menu = $menuId > 0 ? $this->menuService->get($menuId) : null;
        if (!$menu) {
            $menu = ['menuId' => $menuId, 'menuName' => $menuId === 0 ? '主目录' : ''];
        }
        return View::fetch('menu/tree', [
            'menu'      => $menu,
            'menuId'    => $menuId,
            'excludeId' => $excludeId,
        ]);
    }
}
