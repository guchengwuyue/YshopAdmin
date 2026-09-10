<?php
declare(strict_types=1);

namespace app\service;

use app\common\Auth;
use app\common\PageHelper;
use think\facade\Db;

/**
 * 岗位管理
 */
class PostService
{
    /**
     * 查询岗位列表
     * @param array $params 筛选条件（postCode / postName / status）
     * @param bool $export 是否导出（不分页）
     */
    public function selectList(array $params = [], bool $export = false): array
    {
        if ($export) {
            PageHelper::startExport();
        } else {
            PageHelper::startPage();
        }
        $query = Db::name('sys_post');
        $postCode = $params['postCode'] ?? $params['post_code'] ?? '';
        if ($postCode !== '') {
            $query->whereLike('post_code', '%' . $postCode . '%');
        }
        $postName = $params['postName'] ?? $params['post_name'] ?? '';
        if ($postName !== '') {
            $query->whereLike('post_name', '%' . $postName . '%');
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        [$rows, $total] = PageHelper::paginate($query);
        return [PageHelper::camelRows($rows), $total];
    }

    /**
     * 查询全部岗位
     */
    public function selectAll(): array
    {
        return PageHelper::camelRows(Db::name('sys_post')->order('post_sort')->select()->toArray());
    }

    /**
     * 根据岗位 ID 查询详情
     */
    public function get(int $postId): ?array
    {
        $row = Db::name('sys_post')->where('post_id', $postId)->find();
        return $row ? PageHelper::camelKeys($row) : null;
    }

    /**
     * 新增岗位
     */
    public function insert(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $row = [
            'post_code'   => $data['post_code'] ?? '',
            'post_name'   => $data['post_name'] ?? '',
            'post_sort'   => $data['post_sort'] ?? 0,
            'status'      => $data['status'] ?? '0',
            'remark'      => $data['remark'] ?? '',
            'create_by'   => Auth::getLoginName(),
            'create_time' => date('Y-m-d H:i:s'),
        ];
        return (int) Db::name('sys_post')->insertGetId($row);
    }

    /**
     * 修改岗位
     */
    public function update(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $postId = (int) ($data['post_id'] ?? 0);
        return Db::name('sys_post')->where('post_id', $postId)->update([
            'post_code'   => $data['post_code'] ?? '',
            'post_name'   => $data['post_name'] ?? '',
            'post_sort'   => $data['post_sort'] ?? 0,
            'status'      => $data['status'] ?? '0',
            'remark'      => $data['remark'] ?? '',
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 删除岗位
     * @param string $ids 岗位 ID，逗号分隔
     */
    public function delete(string $ids): int
    {
        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        if (!$idArr) {
            return 0;
        }
        Db::name('sys_user_post')->whereIn('post_id', $idArr)->delete();
        return Db::name('sys_post')->whereIn('post_id', $idArr)->delete();
    }
}
