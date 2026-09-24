<?php
declare(strict_types=1);

namespace app\service;

use app\common\Auth;
use app\common\Entry;
use app\common\PageHelper;
use app\common\TreeUtils;
use think\facade\Db;

/**
 * 菜单管理
 */
class MenuService
{
    /**
     * 按用户查询侧边栏菜单树
     */
    public function selectMenusByUserId(int $userId): array
    {
        return $this->prefixAdminMenuUrls(TreeUtils::buildMenuTree($this->selectSidebarRows($userId)));
    }

    /**
     * 管理端 SPA 动态路由（getRouters）。路径不带 /yshopadmin 前缀。
     * @return array<int, array<string, mixed>>
     */
    public function selectRouters(int $userId): array
    {
        return $this->toVueRouters(TreeUtils::buildMenuTree($this->selectSidebarRows($userId)), true);
    }

    /**
     * 当前用户可见的目录与菜单（不含按钮）
     * @return array<int, array<string, mixed>>
     */
    protected function selectSidebarRows(int $userId): array
    {
        if ($userId === 1 || Auth::isAdmin($userId)) {
            return Db::name('sys_menu')
                ->whereIn('menu_type', ['M', 'C'])
                ->where('visible', '0')
                ->order('parent_id, order_num')
                ->select()
                ->toArray();
        }
        return Db::name('sys_menu')->alias('m')
            ->join('sys_role_menu rm', 'm.menu_id = rm.menu_id')
            ->join('sys_user_role ur', 'rm.role_id = ur.role_id')
            ->join('sys_role r', 'r.role_id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->whereIn('m.menu_type', ['M', 'C'])
            ->where('m.visible', '0')
            ->where('r.status', '0')
            ->where('r.del_flag', '0')
            ->field('distinct m.menu_id,m.menu_name,m.parent_id,m.order_num,m.url,m.target,m.menu_type,m.visible,m.is_refresh,m.perms,m.icon')
            ->order('m.parent_id, m.order_num')
            ->select()
            ->toArray();
    }

    /**
     * Prefix relative admin menu URLs with /yshopadmin for dual-entry.
     *
     * @param array<int, array<string, mixed>> $menus
     * @return array<int, array<string, mixed>>
     */
    protected function prefixAdminMenuUrls(array $menus): array
    {
        $root = Entry::adminRoot();
        foreach ($menus as &$menu) {
            $url = (string) ($menu['url'] ?? '');
            if ($url !== '' && $url !== '#' && !preg_match('#^(https?:)?//#i', $url) && str_starts_with($url, '/')) {
                if (!str_starts_with($url, $root . '/') && $url !== $root) {
                    $menu['url'] = $root . $url;
                }
            }
            if (!empty($menu['children']) && is_array($menu['children'])) {
                $menu['children'] = $this->prefixAdminMenuUrls($menu['children']);
            }
        }
        unset($menu);
        return $menus;
    }

    /**
     * 查询菜单列表
     * @param array $params 筛选条件（menuName / visible）
     */
    public function selectMenuList(array $params = []): array
    {
        $query = Db::name('sys_menu');
        $menuName = $params['menuName'] ?? $params['menu_name'] ?? '';
        if ($menuName !== '') {
            $query->whereLike('menu_name', '%' . $menuName . '%');
        }
        if (isset($params['visible']) && $params['visible'] !== '') {
            $query->where('visible', $params['visible']);
        }
        $list = $query->order('parent_id, order_num')->select()->toArray();
        return PageHelper::camelRows($list);
    }

    /**
     * 根据菜单 ID 查询详情
     */
    public function get(int $menuId): ?array
    {
        $row = Db::name('sys_menu')->where('menu_id', $menuId)->find();
        return $row ? PageHelper::camelKeys($row) : null;
    }

    /**
     * 新增菜单
     */
    public function insert(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $allow = ['menu_name', 'parent_id', 'order_num', 'url', 'target', 'menu_type', 'visible', 'is_refresh', 'perms', 'icon', 'remark'];
        $row = array_intersect_key($data, array_flip($allow));
        $row['create_by'] = Auth::getLoginName();
        $row['create_time'] = date('Y-m-d H:i:s');
        return (int) Db::name('sys_menu')->insertGetId($row);
    }

    /**
     * 修改菜单
     */
    public function update(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $menuId = (int) ($data['menu_id'] ?? 0);
        $allow = ['menu_name', 'parent_id', 'order_num', 'url', 'target', 'menu_type', 'visible', 'is_refresh', 'perms', 'icon', 'remark'];
        $row = array_intersect_key($data, array_flip($allow));
        $row['update_by'] = Auth::getLoginName();
        $row['update_time'] = date('Y-m-d H:i:s');
        return Db::name('sys_menu')->where('menu_id', $menuId)->update($row);
    }

    /**
     * 删除菜单
     */
    public function delete(int $menuId): int
    {
        $hasChild = Db::name('sys_menu')->where('parent_id', $menuId)->count();
        if ($hasChild > 0) {
            throw new \RuntimeException('存在子菜单,不允许删除');
        }
        Db::name('sys_role_menu')->where('menu_id', $menuId)->delete();
        return Db::name('sys_menu')->where('menu_id', $menuId)->delete();
    }

    /**
     * 角色菜单树数据（ztree，可按角色勾选）
     * @param int|null $roleId 角色 ID（可选，用于回显已授权菜单）
     */
    public function roleMenuTreeData(?int $roleId = null): array
    {
        $menus = Db::name('sys_menu')->order('parent_id, order_num')->select()->toArray();
        $checked = [];
        if ($roleId) {
            $checked = array_map('intval', Db::name('sys_role_menu')->where('role_id', $roleId)->column('menu_id'));
        }
        $nodes = [];
        foreach ($menus as $m) {
            $nodes[] = [
                'id'      => (int) $m['menu_id'],
                'pId'     => (int) $m['parent_id'],
                'name'    => $m['menu_name'],
                'title'   => $m['menu_name'],
                'checked' => in_array((int) $m['menu_id'], $checked, true),
                'open'    => true,
            ];
        }
        return $nodes;
    }

    /**
     * 菜单树数据（ztree，含根节点）
     * @param int $excludeId 排除的菜单 ID（含其下级，编辑上级时用）
     */
    public function menuTreeData(int $excludeId = 0): array
    {
        $menus = Db::name('sys_menu')->order('parent_id, order_num')->select()->toArray();
        $excludeIds = [];
        if ($excludeId > 0) {
            $excludeIds = $this->collectMenuSelfAndChildren($menus, $excludeId);
        }
        $nodes = [['id' => 0, 'pId' => 0, 'name' => '主目录', 'title' => '主目录', 'open' => true]];
        foreach ($menus as $m) {
            $id = (int) $m['menu_id'];
            if (isset($excludeIds[$id])) {
                continue;
            }
            $nodes[] = [
                'id'    => $id,
                'pId'   => (int) $m['parent_id'],
                'name'  => $m['menu_name'],
                'title' => $m['menu_name'],
                'open'  => true,
            ];
        }
        return $nodes;
    }

    /**
     * 收集菜单自身及全部下级 ID
     * @return array<int, true>
     */
    private function collectMenuSelfAndChildren(array $menus, int $menuId): array
    {
        $exclude = [$menuId => true];
        $changed = true;
        while ($changed) {
            $changed = false;
            foreach ($menus as $m) {
                $id = (int) $m['menu_id'];
                $pid = (int) $m['parent_id'];
                if (!isset($exclude[$id]) && isset($exclude[$pid])) {
                    $exclude[$id] = true;
                    $changed = true;
                }
            }
        }
        return $exclude;
    }

    /**
     * 菜单树转 Vue Router 记录（RuoYi getRouters 形态）
     * @param array<int, array<string, mixed>> $menus
     * @return array<int, array<string, mixed>>
     */
    protected function toVueRouters(array $menus, bool $top): array
    {
        $routes = [];
        foreach ($menus as $menu) {
            $children = is_array($menu['children'] ?? null) ? $menu['children'] : [];
            $childRoutes = $children !== [] ? $this->toVueRouters($children, false) : [];
            $url = (string) ($menu['url'] ?? '');
            $menuId = (int) ($menu['menuId'] ?? 0);
            $external = preg_match('#^https?://#i', $url) === 1;
            $isDir = $childRoutes !== [] || (string) ($menu['menuType'] ?? '') === 'M';
            $node = [
                'name'      => 'menu' . $menuId,
                'path'      => $isDir ? $this->directoryPath($menuId, $top) : $this->leafPath($url, $menuId),
                'hidden'    => (string) ($menu['visible'] ?? '0') === '1',
                'component' => $this->routerComponent($isDir, $top, $external, $url),
                'meta'      => [
                    'title'   => (string) ($menu['menuName'] ?? ''),
                    'icon'    => (string) ($menu['icon'] ?? ''),
                    'noCache' => (string) ($menu['isRefresh'] ?? '1') === '1',
                    'link'    => $external ? $url : null,
                ],
            ];
            if ($childRoutes !== []) {
                $node['alwaysShow'] = true;
                $node['children'] = $childRoutes;
            }
            $routes[] = $node;
        }
        return $routes;
    }

    protected function routerComponent(bool $isDir, bool $top, bool $external, string $url): string
    {
        if ($external) {
            return 'InnerLink';
        }
        if ($isDir && $top) {
            return 'Layout';
        }
        if ($isDir) {
            return 'ParentView';
        }
        return $this->viewComponent($url);
    }

    protected function viewComponent(string $url): string
    {
        $path = trim($url, '/');
        if ($path === '' || $path === '#') {
            return 'placeholder/index';
        }
        return $path . '/index';
    }

    protected function directoryPath(int $menuId, bool $top): string
    {
        return ($top ? '/dir-' : 'dir-') . $menuId;
    }

    protected function leafPath(string $url, int $menuId): string
    {
        if (preg_match('#^https?://#i', $url) === 1) {
            return '/external/' . $menuId;
        }
        $clean = trim($url, '/');
        if ($clean === '' || $clean === '#') {
            return '/m' . $menuId;
        }
        return '/' . $clean;
    }
}
