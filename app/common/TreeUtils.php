<?php
declare(strict_types=1);

namespace app\common;

/**
 * 树形结构工具
 */
class TreeUtils
{
    /**
     * 扁平列表构建树
     * @param array $list 扁平列表
     * @param string $idKey 节点 ID 字段
     * @param string $parentKey 父节点字段
     * @param string $childrenKey 子节点字段
     * @param int|string $rootParentId 根父级 ID
     */
    public static function buildTree(
        array $list,
        string $idKey = 'id',
        string $parentKey = 'parentId',
        string $childrenKey = 'children',
        $rootParentId = 0
    ): array {
        $map = [];
        $tree = [];

        foreach ($list as $item) {
            $item[$childrenKey] = $item[$childrenKey] ?? [];
            $map[$item[$idKey]] = $item;
        }

        foreach ($map as $id => &$node) {
            $pid = $node[$parentKey] ?? $rootParentId;
            if ((string) $pid === (string) $rootParentId) {
                $tree[] = &$node;
            } elseif (isset($map[$pid])) {
                $map[$pid][$childrenKey][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }
        unset($node);

        return $tree;
    }

    /**
     * 构建菜单树
     */
    public static function buildMenuTree(array $menus): array
    {
        $list = [];
        foreach ($menus as $m) {
            $id = $m['menu_id'] ?? $m['menuId'] ?? 0;
            $pid = $m['parent_id'] ?? $m['parentId'] ?? 0;
            $item = PageHelper::camelKeys($m);
            $item['menuId'] = $id;
            $item['parentId'] = $pid;
            $item['children'] = [];
            $list[] = $item;
        }
        return self::buildTree($list, 'menuId', 'parentId', 'children', 0);
    }

    /**
     * 构建部门树
     */
    public static function buildDeptTree(array $depts): array
    {
        $list = [];
        foreach ($depts as $d) {
            $item = PageHelper::camelKeys($d);
            $item['deptId'] = $item['deptId'] ?? ($d['dept_id'] ?? 0);
            $item['parentId'] = $item['parentId'] ?? ($d['parent_id'] ?? 0);
            $item['children'] = [];
            $list[] = $item;
        }
        return self::buildTree($list, 'deptId', 'parentId', 'children', 0);
    }
}
