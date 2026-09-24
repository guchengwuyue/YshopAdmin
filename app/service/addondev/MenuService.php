<?php
declare(strict_types=1);

namespace app\service\addondev;

use app\common\addon\AddonManager;
use app\common\Auth;
use app\common\PageHelper;
use think\facade\Db;

/**
 * 插件菜单维护
 */
class MenuService
{
    /**
     * 菜单全量列表（不分页，便于组树）
     * @return array{0: array, 1: int}
     */
    public function selectList(array $params = []): array
    {
        $name = trim((string) ($params['name'] ?? ''));
        if ($name === '') {
            return [[], 0];
        }
        $rows = Db::name('sys_menu')
            ->where('remark', $this->mark($name))
            ->order('parent_id, order_num')
            ->select()
            ->toArray();
        $camel = PageHelper::camelRows($rows);
        return [$camel, count($camel)];
    }

    /**
     * 菜单详情（须属于该插件）
     */
    public function get(int $id, string $name): ?array
    {
        $name = trim($name);
        if ($id <= 0 || $name === '') {
            return null;
        }
        $row = Db::name('sys_menu')
            ->where('menu_id', $id)
            ->where('remark', $this->mark($name))
            ->find();
        return $row ? PageHelper::camelKeys($row) : null;
    }

    /**
     * 新增菜单（强制 remark=addon:{name}）
     */
    public function insert(string $name, array $data): int
    {
        $name = $this->assertAddon($name);
        $data = PageHelper::snakeParams($data);
        $parentId = (int) ($data['parent_id'] ?? 0);
        $this->assertParentBelongs($name, $parentId);

        $allow = ['menu_name', 'parent_id', 'order_num', 'url', 'target', 'menu_type', 'visible', 'is_refresh', 'perms', 'icon'];
        $row = array_intersect_key($data, array_flip($allow));
        $row['parent_id'] = $parentId;
        $row['remark'] = $this->mark($name);
        $row['create_by'] = Auth::getLoginName();
        $row['create_time'] = date('Y-m-d H:i:s');
        if (!isset($row['url']) || $row['url'] === '') {
            $row['url'] = '#';
        }
        if (!isset($row['icon']) || $row['icon'] === '') {
            $row['icon'] = '#';
        }

        $id = (int) Db::name('sys_menu')->insertGetId($row);
        $this->exportMenuPhp($name);
        return $id;
    }

    /**
     * 修改菜单（不可改掉 remark）
     */
    public function update(string $name, array $data): int
    {
        $name = $this->assertAddon($name);
        $data = PageHelper::snakeParams($data);
        $menuId = (int) ($data['menu_id'] ?? 0);
        if ($menuId <= 0) {
            throw new \InvalidArgumentException('菜单 ID 无效');
        }
        $this->assertOwned($name, $menuId);

        $parentId = (int) ($data['parent_id'] ?? 0);
        $this->assertParentBelongs($name, $parentId);
        if ($parentId === $menuId) {
            throw new \InvalidArgumentException('上级菜单不能是自身');
        }
        $exclude = $this->collectSelfAndChildren($name, $menuId);
        if ($parentId > 0 && isset($exclude[$parentId])) {
            throw new \InvalidArgumentException('上级菜单不能是自身的下级');
        }

        $allow = ['menu_name', 'parent_id', 'order_num', 'url', 'target', 'menu_type', 'visible', 'is_refresh', 'perms', 'icon'];
        $row = array_intersect_key($data, array_flip($allow));
        $row['parent_id'] = $parentId;
        $row['remark'] = $this->mark($name);
        $row['update_by'] = Auth::getLoginName();
        $row['update_time'] = date('Y-m-d H:i:s');

        Db::name('sys_menu')->where('menu_id', $menuId)->where('remark', $this->mark($name))->update($row);
        $this->exportMenuPhp($name);
        return 1;
    }

    /**
     * 删除菜单（有子则不允许）
     */
    public function delete(string $name, int $menuId): int
    {
        $name = $this->assertAddon($name);
        if ($menuId <= 0) {
            throw new \InvalidArgumentException('菜单 ID 无效');
        }
        $this->assertOwned($name, $menuId);

        $hasChild = Db::name('sys_menu')->where('parent_id', $menuId)->count();
        if ($hasChild > 0) {
            throw new \RuntimeException('存在子菜单,不允许删除');
        }

        Db::name('sys_role_menu')->where('menu_id', $menuId)->delete();
        $n = Db::name('sys_menu')->where('menu_id', $menuId)->where('remark', $this->mark($name))->delete();
        $this->exportMenuPhp($name);
        return $n;
    }

    /**
     * 上级菜单树选（ztree 形态，含根节点）
     */
    public function treeForSelect(string $name, int $excludeId = 0): array
    {
        $name = trim($name);
        if ($name === '') {
            return [['id' => 0, 'pId' => 0, 'name' => '主目录', 'title' => '主目录', 'open' => true]];
        }
        $menus = Db::name('sys_menu')
            ->where('remark', $this->mark($name))
            ->order('parent_id, order_num')
            ->select()
            ->toArray();
        $excludeIds = $excludeId > 0 ? $this->collectSelfAndChildrenFromRows($menus, $excludeId) : [];
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
     * 从 DB 组树写回 addons/{name}/menu.php
     */
    public function exportMenuPhp(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            return;
        }
        $dir = addon_path($name);
        if (!is_dir($dir)) {
            throw new \RuntimeException('插件目录不存在');
        }
        $rows = Db::name('sys_menu')
            ->where('remark', $this->mark($name))
            ->order('parent_id, order_num')
            ->select()
            ->toArray();
        $tree = $this->buildExportTree($rows, 0);
        $export = [];
        foreach ($tree as $node) {
            $export[] = $this->stripParentIdExceptRoot($node, true);
        }
        $php = "<?php\ndeclare(strict_types=1);\n\n"
            . "/**\n * 插件菜单（M/C/F）；运行时 remark=addon:{$name}\n */\n"
            . 'return ' . $this->exportVar($export) . ";\n";
        $file = $dir . 'menu.php';
        if (file_put_contents($file, $php) === false) {
            throw new \RuntimeException('写入 menu.php 失败');
        }
    }

    /**
     * 从 menu.php 同步
     */
    public function sync(string $name): int
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('请指定插件标识');
        }
        if (!get_addon_info($name)) {
            throw new \RuntimeException('插件不存在');
        }
        AddonManager::instance()->syncMenus($name, true);
        return 1;
    }

    private function mark(string $name): string
    {
        return 'addon:' . $name;
    }

    private function assertAddon(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('请指定插件标识');
        }
        if (!get_addon_info($name)) {
            throw new \RuntimeException('插件不存在');
        }
        return $name;
    }

    private function assertOwned(string $name, int $menuId): void
    {
        $exists = Db::name('sys_menu')
            ->where('menu_id', $menuId)
            ->where('remark', $this->mark($name))
            ->count();
        if ($exists <= 0) {
            throw new \RuntimeException('菜单不属于该插件或不存在');
        }
    }

    private function assertParentBelongs(string $name, int $parentId): void
    {
        if ($parentId === 0) {
            return;
        }
        $exists = Db::name('sys_menu')
            ->where('menu_id', $parentId)
            ->where('remark', $this->mark($name))
            ->count();
        if ($exists <= 0) {
            throw new \InvalidArgumentException('上级菜单须为同插件菜单');
        }
    }

    /**
     * @return array<int, true>
     */
    private function collectSelfAndChildren(string $name, int $menuId): array
    {
        $menus = Db::name('sys_menu')
            ->where('remark', $this->mark($name))
            ->field('menu_id,parent_id')
            ->select()
            ->toArray();
        return $this->collectSelfAndChildrenFromRows($menus, $menuId);
    }

    /**
     * @return array<int, true>
     */
    private function collectSelfAndChildrenFromRows(array $menus, int $menuId): array
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
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function buildExportTree(array $rows, int $parentId): array
    {
        $tree = [];
        foreach ($rows as $row) {
            if ((int) $row['parent_id'] !== $parentId) {
                continue;
            }
            $node = [
                'menu_name' => (string) ($row['menu_name'] ?? ''),
                'parent_id' => (int) $row['parent_id'],
                'order_num' => (int) ($row['order_num'] ?? 0),
                'url'       => (string) ($row['url'] ?? '#'),
                'menu_type' => (string) ($row['menu_type'] ?? 'C'),
                'visible'   => (string) ($row['visible'] ?? '0'),
                'perms'     => (string) ($row['perms'] ?? ''),
                'icon'      => (string) ($row['icon'] ?? '#'),
            ];
            $children = $this->buildExportTree($rows, (int) $row['menu_id']);
            if ($children !== []) {
                $node['children'] = $children;
            }
            $tree[] = $node;
        }
        return $tree;
    }

    /**
     * 子节点去掉 parent_id（与现有 menu.php 风格一致，仅根保留）
     * @param array<string, mixed> $node
     * @return array<string, mixed>
     */
    private function stripParentIdExceptRoot(array $node, bool $isRoot): array
    {
        if (!$isRoot) {
            unset($node['parent_id']);
        }
        if (!empty($node['children']) && is_array($node['children'])) {
            $kids = [];
            foreach ($node['children'] as $child) {
                $kids[] = $this->stripParentIdExceptRoot($child, false);
            }
            $node['children'] = $kids;
        }
        return $node;
    }

    /**
     * 导出可读 PHP 数组
     */
    private function exportVar(mixed $value, int $indent = 0): string
    {
        $pad = str_repeat('    ', $indent);
        $pad1 = str_repeat('    ', $indent + 1);
        if (!is_array($value)) {
            return var_export($value, true);
        }
        $isList = array_is_list($value);
        $parts = [];
        foreach ($value as $k => $v) {
            if (is_array($v)) {
                $exported = $this->exportVar($v, $indent + 1);
            } else {
                $exported = var_export($v, true);
            }
            if ($isList) {
                $parts[] = $pad1 . $exported;
            } else {
                $parts[] = $pad1 . var_export((string) $k, true) . ' => ' . $exported;
            }
        }
        if ($parts === []) {
            return '[]';
        }
        return "[\n" . implode(",\n", $parts) . ",\n" . $pad . ']';
    }
}
