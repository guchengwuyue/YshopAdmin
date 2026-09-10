<?php
declare(strict_types=1);

namespace app\service;

use app\common\PageHelper;
use think\facade\Db;

/**
 * 调度日志
 */
class JobLogService
{
    /**
     * 查询调度日志列表
     * @param array $params 筛选条件（jobName / jobGroup / status / jobId）
     * @param bool $export 是否导出（不分页）
     */
    public function selectList(array $params = [], bool $export = false): array
    {
        if ($export) {
            PageHelper::startExport();
        } else {
            PageHelper::startPage();
        }
        $query = Db::name('sys_job_log');
        $jobName = $params['jobName'] ?? $params['job_name'] ?? '';
        if ($jobName !== '') {
            $query->whereLike('job_name', '%' . $jobName . '%');
        }
        $jobGroup = $params['jobGroup'] ?? $params['job_group'] ?? '';
        if ($jobGroup !== '') {
            $query->where('job_group', $jobGroup);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        $jobId = $params['jobId'] ?? $params['job_id'] ?? '';
        if ($jobId !== '' && $jobId !== null) {
            $job = Db::name('sys_job')->where('job_id', (int) $jobId)->find();
            if ($job) {
                $query->where('job_name', $job['job_name'])->where('job_group', $job['job_group']);
            }
        }
        $query->order('job_log_id', 'desc');
        [$rows, $total] = PageHelper::paginate($query);
        return [PageHelper::camelRows($rows), $total];
    }

    /**
     * 根据日志 ID 查询详情
     */
    public function get(int $jobLogId): ?array
    {
        $row = Db::name('sys_job_log')->where('job_log_id', $jobLogId)->find();
        return $row ? PageHelper::camelKeys($row) : null;
    }

    /**
     * 删除调度日志
     * @param string $ids 日志 ID，逗号分隔
     */
    public function delete(string $ids): int
    {
        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        if (!$idArr) {
            return 0;
        }
        return Db::name('sys_job_log')->whereIn('job_log_id', $idArr)->delete();
    }

    /**
     * 清空调度日志
     */
    public function clean(): int
    {
        return Db::execute('TRUNCATE TABLE sys_job_log');
    }
}
