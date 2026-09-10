<?php
declare(strict_types=1);

namespace app\service;

use app\common\PageHelper;
use think\facade\Db;

/**
 * 登录日志
 */
class LogininforService
{
    /**
     * 查询登录日志列表
     * @param array $params 筛选条件（loginName / ipaddr / status / beginTime / endTime）
     * @param bool $export 是否导出（不分页）
     */
    public function selectList(array $params = [], bool $export = false): array
    {
        if ($export) {
            PageHelper::startExport();
        } else {
            PageHelper::startPage();
        }
        $query = Db::name('sys_logininfor');
        $loginName = $params['loginName'] ?? $params['login_name'] ?? '';
        if ($loginName !== '') {
            $query->whereLike('login_name', '%' . $loginName . '%');
        }
        $ipaddr = $params['ipaddr'] ?? '';
        if ($ipaddr !== '') {
            $query->whereLike('ipaddr', '%' . $ipaddr . '%');
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        $beginTime = $params['beginTime'] ?? ($params['params']['beginTime'] ?? '');
        $endTime = $params['endTime'] ?? ($params['params']['endTime'] ?? '');
        if ($beginTime !== '') {
            $query->where('login_time', '>=', $beginTime);
        }
        if ($endTime !== '') {
            $query->where('login_time', '<=', $endTime . ' 23:59:59');
        }
        [$rows, $total] = PageHelper::paginate($query);
        return [PageHelper::camelRows($rows), $total];
    }

    /**
     * 删除登录日志
     * @param string $ids 日志 ID，逗号分隔
     */
    public function delete(string $ids): int
    {
        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        if (!$idArr) {
            return 0;
        }
        return Db::name('sys_logininfor')->whereIn('info_id', $idArr)->delete();
    }

    /**
     * 清空登录日志
     */
    public function clean(): int
    {
        return Db::execute('TRUNCATE TABLE sys_logininfor');
    }
}
