<?php
declare(strict_types=1);

namespace app\service;

use app\common\Auth;
use app\common\PageHelper;
use think\facade\Db;

/**
 * 字典数据管理
 */
class DictDataService
{
    /**
     * 查询字典数据列表
     * @param array $params 筛选条件（dictType / dictLabel / status）
     * @param bool $export 是否导出（不分页）
     */
    public function selectList(array $params = [], bool $export = false): array
    {
        if ($export) {
            PageHelper::startExport();
        } else {
            PageHelper::startPage();
        }
        $query = Db::name('sys_dict_data');
        $dictType = $params['dictType'] ?? $params['dict_type'] ?? '';
        if ($dictType !== '') {
            $query->where('dict_type', $dictType);
        }
        $dictLabel = $params['dictLabel'] ?? $params['dict_label'] ?? '';
        if ($dictLabel !== '') {
            $query->whereLike('dict_label', '%' . $dictLabel . '%');
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        [$rows, $total] = PageHelper::paginate($query);
        return [PageHelper::camelRows($rows), $total];
    }

    /**
     * 按字典类型查询字典数据（带缓存）
     */
    public function selectByType(string $dictType): array
    {
        $rows = (new CacheMonitorService())->getDictCached($dictType);
        return PageHelper::camelRows($rows);
    }

    /**
     * 根据字典编码查询详情
     */
    public function get(int $dictCode): ?array
    {
        $row = Db::name('sys_dict_data')->where('dict_code', $dictCode)->find();
        return $row ? PageHelper::camelKeys($row) : null;
    }

    /**
     * 新增字典数据
     */
    public function insert(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $row = [
            'dict_sort'   => $data['dict_sort'] ?? 0,
            'dict_label'  => $data['dict_label'] ?? '',
            'dict_value'  => $data['dict_value'] ?? '',
            'dict_type'   => $data['dict_type'] ?? '',
            'css_class'   => $data['css_class'] ?? '',
            'list_class'  => $data['list_class'] ?? '',
            'is_default'  => $data['is_default'] ?? 'N',
            'status'      => $data['status'] ?? '0',
            'remark'      => $data['remark'] ?? '',
            'create_by'   => Auth::getLoginName(),
            'create_time' => date('Y-m-d H:i:s'),
        ];
        $id = (int) Db::name('sys_dict_data')->insertGetId($row);
        (new CacheMonitorService())->clearCacheName(CacheMonitorService::NAME_DICT);
        return $id;
    }

    /**
     * 修改字典数据
     */
    public function update(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $dictCode = (int) ($data['dict_code'] ?? 0);
        $n = Db::name('sys_dict_data')->where('dict_code', $dictCode)->update([
            'dict_sort'   => $data['dict_sort'] ?? 0,
            'dict_label'  => $data['dict_label'] ?? '',
            'dict_value'  => $data['dict_value'] ?? '',
            'dict_type'   => $data['dict_type'] ?? '',
            'css_class'   => $data['css_class'] ?? '',
            'list_class'  => $data['list_class'] ?? '',
            'is_default'  => $data['is_default'] ?? 'N',
            'status'      => $data['status'] ?? '0',
            'remark'      => $data['remark'] ?? '',
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
        (new CacheMonitorService())->clearCacheName(CacheMonitorService::NAME_DICT);
        return $n;
    }

    /**
     * 删除字典数据
     * @param string $ids 字典编码，逗号分隔
     */
    public function delete(string $ids): int
    {
        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        if (!$idArr) {
            return 0;
        }
        $n = Db::name('sys_dict_data')->whereIn('dict_code', $idArr)->delete();
        (new CacheMonitorService())->clearCacheName(CacheMonitorService::NAME_DICT);
        return $n;
    }
}
