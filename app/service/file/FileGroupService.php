<?php
declare(strict_types=1);

namespace app\service\file;

use app\common\Auth;
use app\common\PageHelper;
use app\common\TreeUtils;
use think\facade\Db;

/**
 * 文件分组
 */
class FileGroupService
{
    /**
     * 分组树
     */
    public function tree(): array
    {
        $list = Db::name('ys_file_group')->where('del_flag', '0')
            ->order('parent_id, order_num, id')->select()->toArray();
        $nodes = [];
        foreach ($list as $row) {
            $item = PageHelper::camelKeys($row);
            $item['id'] = (int) ($item['id'] ?? 0);
            $item['parentId'] = (int) ($item['parentId'] ?? 0);
            $item['children'] = [];
            $nodes[] = $item;
        }
        return TreeUtils::buildTree($nodes, 'id', 'parentId', 'children', 0);
    }

    /**
     * 新增分组
     */
    public function insert(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('请输入分类名称');
        }
        $parentId = (int) ($data['parent_id'] ?? 0);
        if ($parentId > 0 && !$this->exists($parentId)) {
            throw new \InvalidArgumentException('上级分类不存在');
        }
        return (int) Db::name('ys_file_group')->insertGetId([
            'parent_id'   => max(0, $parentId),
            'name'        => $name,
            'order_num'   => (int) ($data['order_num'] ?? 0),
            'create_by'   => Auth::getLoginName(),
            'create_time' => date('Y-m-d H:i:s'),
            'del_flag'    => '0',
        ]);
    }

    /**
     * 重命名
     */
    public function update(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $id = (int) ($data['id'] ?? 0);
        $name = trim((string) ($data['name'] ?? ''));
        if ($id <= 0 || $name === '') {
            throw new \InvalidArgumentException('参数错误');
        }
        $row = Db::name('ys_file_group')->where('id', $id)->where('del_flag', '0')->find();
        if (!$row) {
            throw new \InvalidArgumentException('分类不存在');
        }
        if ((string) $row['name'] === $name) {
            return 1;
        }
        return (int) Db::name('ys_file_group')->where('id', $id)->update([
            'name'        => $name,
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 删除分组。仍有子分组或文件时拒绝
     */
    public function delete(int $id): int
    {
        if ($id <= 0 || !$this->exists($id)) {
            return 0;
        }
        $child = (int) Db::name('ys_file_group')->where('parent_id', $id)->where('del_flag', '0')->count();
        if ($child > 0) {
            throw new \RuntimeException('请先删除子分类');
        }
        $files = (int) Db::name('ys_file')->where('group_id', $id)->where('del_flag', '0')->count();
        if ($files > 0) {
            throw new \RuntimeException('分类下仍有文件，无法删除');
        }
        return (int) Db::name('ys_file_group')->where('id', $id)->where('del_flag', '0')->update([
            'del_flag'    => '2',
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 分组是否存在
     */
    protected function exists(int $id): bool
    {
        return (bool) Db::name('ys_file_group')->where('id', $id)->where('del_flag', '0')->find();
    }
}
