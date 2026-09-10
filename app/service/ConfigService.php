<?php
declare(strict_types=1);

namespace app\service;

use app\common\Auth;
use app\common\PageHelper;
use think\facade\Db;

/**
 * 参数配置管理
 */
class ConfigService
{
    /**
     * 查询参数配置列表
     * @param array $params 筛选条件（configName / configKey / configType）
     * @param bool $export 是否导出（不分页）
     */
    public function selectList(array $params = [], bool $export = false): array
    {
        if ($export) {
            PageHelper::startExport();
        } else {
            PageHelper::startPage();
        }
        $query = Db::name('sys_config');
        $configName = $params['configName'] ?? $params['config_name'] ?? '';
        if ($configName !== '') {
            $query->whereLike('config_name', '%' . $configName . '%');
        }
        $configKey = $params['configKey'] ?? $params['config_key'] ?? '';
        if ($configKey !== '') {
            $query->whereLike('config_key', '%' . $configKey . '%');
        }
        if (isset($params['configType']) && $params['configType'] !== '') {
            $query->where('config_type', $params['configType']);
        }
        [$rows, $total] = PageHelper::paginate($query);
        return [PageHelper::camelRows($rows), $total];
    }

    /**
     * 根据参数 ID 查询详情
     */
    public function get(int $configId): ?array
    {
        $row = Db::name('sys_config')->where('config_id', $configId)->find();
        return $row ? PageHelper::camelKeys($row) : null;
    }

    /**
     * 根据参数键名获取值（带缓存）
     * @param string $configKey 参数键名
     * @param string $default 默认值
     */
    public function getKey(string $configKey, string $default = ''): string
    {
        $cached = (new CacheMonitorService())->getConfigCached($configKey);
        if ($cached !== null) {
            return $cached;
        }
        return $default;
    }

    /**
     * 新增参数配置
     */
    public function insert(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $row = [
            'config_name'  => $data['config_name'] ?? '',
            'config_key'   => $data['config_key'] ?? '',
            'config_value' => $data['config_value'] ?? '',
            'config_type'  => $data['config_type'] ?? 'N',
            'remark'       => $data['remark'] ?? '',
            'create_by'    => Auth::getLoginName(),
            'create_time'  => date('Y-m-d H:i:s'),
        ];
        $id = (int) Db::name('sys_config')->insertGetId($row);
        (new CacheMonitorService())->warmConfig((string) $row['config_key'], (string) $row['config_value']);
        return $id;
    }

    /**
     * 修改参数配置
     */
    public function update(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $configId = (int) ($data['config_id'] ?? 0);
        $n = Db::name('sys_config')->where('config_id', $configId)->update([
            'config_name'  => $data['config_name'] ?? '',
            'config_key'   => $data['config_key'] ?? '',
            'config_value' => $data['config_value'] ?? '',
            'config_type'  => $data['config_type'] ?? 'N',
            'remark'       => $data['remark'] ?? '',
            'update_by'    => Auth::getLoginName(),
            'update_time'  => date('Y-m-d H:i:s'),
        ]);
        (new CacheMonitorService())->warmConfig((string) ($data['config_key'] ?? ''), (string) ($data['config_value'] ?? ''));
        return $n;
    }

    /**
     * 删除参数配置
     * @param string $ids 参数 ID，逗号分隔
     */
    public function delete(string $ids): int
    {
        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        if (!$idArr) {
            return 0;
        }
        $keys = Db::name('sys_config')->whereIn('config_id', $idArr)->column('config_key');
        $n = Db::name('sys_config')->whereIn('config_id', $idArr)->delete();
        $cache = new CacheMonitorService();
        foreach ($keys as $key) {
            $cache->clearCacheKey(CacheMonitorService::NAME_CONFIG, (string) $key);
        }
        return $n;
    }

    /**
     * 校验参数键名是否唯一
     * @param int|null $configId 排除的参数 ID（修改时用）
     */
    public function checkConfigKeyUnique(string $configKey, ?int $configId = null): bool
    {
        $q = Db::name('sys_config')->where('config_key', $configKey);
        if ($configId) {
            $q->where('config_id', '<>', $configId);
        }
        return $q->count() === 0;
    }
}
