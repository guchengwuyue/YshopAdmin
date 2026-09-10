<?php
declare(strict_types=1);

namespace app\service;

use app\common\Auth;
use app\common\PageHelper;
use think\facade\Db;

/**
 * 字典类型管理
 */
class DictTypeService
{
    /**
     * 查询字典类型列表
     * @param array $params 筛选条件（dictName / dictType / status）
     * @param bool $export 是否导出（不分页）
     */
    public function selectList(array $params = [], bool $export = false): array
    {
        if ($export) {
            PageHelper::startExport();
        } else {
            PageHelper::startPage();
        }
        $query = Db::name('sys_dict_type');
        $dictName = $params['dictName'] ?? $params['dict_name'] ?? '';
        if ($dictName !== '') {
            $query->whereLike('dict_name', '%' . $dictName . '%');
        }
        $dictType = $params['dictType'] ?? $params['dict_type'] ?? '';
        if ($dictType !== '') {
            $query->whereLike('dict_type', '%' . $dictType . '%');
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        [$rows, $total] = PageHelper::paginate($query);
        return [PageHelper::camelRows($rows), $total];
    }

    /**
     * 查询全部字典类型
     */
    public function selectAll(): array
    {
        return PageHelper::camelRows(Db::name('sys_dict_type')->select()->toArray());
    }

    /**
     * 根据字典类型 ID 查询详情
     */
    public function get(int $dictId): ?array
    {
        $row = Db::name('sys_dict_type')->where('dict_id', $dictId)->find();
        return $row ? PageHelper::camelKeys($row) : null;
    }

    /**
     * 新增字典类型
     */
    public function insert(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $row = [
            'dict_name'   => $data['dict_name'] ?? '',
            'dict_type'   => $data['dict_type'] ?? '',
            'status'      => $data['status'] ?? '0',
            'remark'      => $data['remark'] ?? '',
            'create_by'   => Auth::getLoginName(),
            'create_time' => date('Y-m-d H:i:s'),
        ];
        return (int) Db::name('sys_dict_type')->insertGetId($row);
    }

    /**
     * 修改字典类型
     */
    public function update(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $dictId = (int) ($data['dict_id'] ?? 0);
        $old = Db::name('sys_dict_type')->where('dict_id', $dictId)->find();
        $newType = $data['dict_type'] ?? '';
        $n = Db::name('sys_dict_type')->where('dict_id', $dictId)->update([
            'dict_name'   => $data['dict_name'] ?? '',
            'dict_type'   => $newType,
            'status'      => $data['status'] ?? '0',
            'remark'      => $data['remark'] ?? '',
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
        if ($old && $old['dict_type'] !== $newType) {
            Db::name('sys_dict_data')->where('dict_type', $old['dict_type'])->update(['dict_type' => $newType]);
        }
        (new CacheMonitorService())->clearCacheName(CacheMonitorService::NAME_DICT);
        return $n;
    }

    /**
     * 删除字典类型（同时删除字典数据）
     * @param string $ids 字典类型 ID，逗号分隔
     */
    public function delete(string $ids): int
    {
        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        if (!$idArr) {
            return 0;
        }
        $types = Db::name('sys_dict_type')->whereIn('dict_id', $idArr)->column('dict_type');
        Db::name('sys_dict_data')->whereIn('dict_type', $types)->delete();
        $n = Db::name('sys_dict_type')->whereIn('dict_id', $idArr)->delete();
        (new CacheMonitorService())->clearCacheName(CacheMonitorService::NAME_DICT);
        return $n;
    }

    /**
     * 校验字典类型是否唯一
     * @param int|null $dictId 排除的字典类型 ID（修改时用）
     */
    public function checkDictTypeUnique(string $dictType, ?int $dictId = null): bool
    {
        $q = Db::name('sys_dict_type')->where('dict_type', $dictType);
        if ($dictId) {
            $q->where('dict_id', '<>', $dictId);
        }
        return $q->count() === 0;
    }
}
