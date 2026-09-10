<?php
declare(strict_types=1);

namespace app\service;

use app\common\PageHelper;
use think\facade\Db;

/**
 * 操作日志
 */
class OperLogService
{
    /**
     * 查询操作日志列表
     * @param array $params 筛选条件（title / operName / businessType / status / beginTime / endTime）
     * @param bool $export 是否导出（不分页）
     */
    public function selectList(array $params = [], bool $export = false): array
    {
        if ($export) {
            PageHelper::startExport();
        } else {
            PageHelper::startPage();
        }
        $query = Db::name('sys_oper_log');
        $title = $params['title'] ?? '';
        if ($title !== '') {
            $query->whereLike('title', '%' . $title . '%');
        }
        $operName = $params['operName'] ?? $params['oper_name'] ?? '';
        if ($operName !== '') {
            $query->whereLike('oper_name', '%' . $operName . '%');
        }
        if (isset($params['businessType']) && $params['businessType'] !== '') {
            $query->where('business_type', $params['businessType']);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        $beginTime = $params['beginTime'] ?? ($params['params']['beginTime'] ?? '');
        $endTime = $params['endTime'] ?? ($params['params']['endTime'] ?? '');
        if ($beginTime !== '') {
            $query->where('oper_time', '>=', $beginTime);
        }
        if ($endTime !== '') {
            $query->where('oper_time', '<=', $endTime . ' 23:59:59');
        }
        [$rows, $total] = PageHelper::paginate($query);
        return [PageHelper::camelRows($rows), $total];
    }

    /**
     * 根据日志 ID 查询详情
     */
    public function get(int $operId): ?array
    {
        $row = Db::name('sys_oper_log')->where('oper_id', $operId)->find();
        return $row ? PageHelper::camelKeys($row) : null;
    }

    /**
     * 删除操作日志
     * @param string $ids 日志 ID，逗号分隔
     */
    public function delete(string $ids): int
    {
        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        if (!$idArr) {
            return 0;
        }
        return Db::name('sys_oper_log')->whereIn('oper_id', $idArr)->delete();
    }

    /**
     * 清空操作日志
     */
    public function clean(): int
    {
        return Db::execute('TRUNCATE TABLE sys_oper_log');
    }
}
