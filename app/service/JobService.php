<?php
declare(strict_types=1);

namespace app\service;

use app\common\Auth;
use app\common\PageHelper;
use app\common\QuartzCron;
use app\job\RyTask;
use think\facade\Db;

/**
 * 定时任务
 */
class JobService
{
    /** 可调用目标白名单 bean => 类名 */
    protected array $whitelist = [
        'ryTask' => RyTask::class,
    ];

    /**
     * 查询定时任务列表
     * @param array $params 筛选条件（jobName / jobGroup / status）
     * @param bool $export 是否导出（不分页）
     */
    public function selectList(array $params = [], bool $export = false): array
    {
        if ($export) {
            PageHelper::startExport();
        } else {
            PageHelper::startPage();
        }
        $query = Db::name('sys_job');
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
        [$rows, $total] = PageHelper::paginate($query);
        return [PageHelper::camelRows($rows), $total];
    }

    /**
     * 根据任务 ID 查询详情
     */
    public function get(int $jobId): ?array
    {
        $row = Db::name('sys_job')->where('job_id', $jobId)->find();
        return $row ? PageHelper::camelKeys($row) : null;
    }

    /**
     * 新增定时任务
     */
    public function insert(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $this->assertInvokeAllowed((string) ($data['invoke_target'] ?? ''));
        $row = [
            'job_name'        => $data['job_name'] ?? '',
            'job_group'       => $data['job_group'] ?? 'DEFAULT',
            'invoke_target'   => $data['invoke_target'] ?? '',
            'cron_expression' => $data['cron_expression'] ?? '',
            'misfire_policy'  => $data['misfire_policy'] ?? '3',
            'concurrent'      => $data['concurrent'] ?? '1',
            'status'          => $data['status'] ?? '1',
            'remark'          => $data['remark'] ?? '',
            'create_by'       => Auth::getLoginName(),
            'create_time'     => date('Y-m-d H:i:s'),
        ];
        return (int) Db::name('sys_job')->insertGetId($row);
    }

    /**
     * 修改定时任务
     */
    public function update(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $jobId = (int) ($data['job_id'] ?? 0);
        $this->assertInvokeAllowed((string) ($data['invoke_target'] ?? ''));
        return Db::name('sys_job')->where('job_id', $jobId)->update([
            'job_name'        => $data['job_name'] ?? '',
            'job_group'       => $data['job_group'] ?? 'DEFAULT',
            'invoke_target'   => $data['invoke_target'] ?? '',
            'cron_expression' => $data['cron_expression'] ?? '',
            'misfire_policy'  => $data['misfire_policy'] ?? '3',
            'concurrent'      => $data['concurrent'] ?? '1',
            'status'          => $data['status'] ?? '0',
            'remark'          => $data['remark'] ?? '',
            'update_by'       => Auth::getLoginName(),
            'update_time'     => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 删除定时任务
     * @param string $ids 任务 ID，逗号分隔
     */
    public function delete(string $ids): int
    {
        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        if (!$idArr) {
            return 0;
        }
        return Db::name('sys_job')->whereIn('job_id', $idArr)->delete();
    }

    /**
     * 修改任务状态
     */
    public function changeStatus(int $jobId, string $status): int
    {
        return Db::name('sys_job')->where('job_id', $jobId)->update([
            'status'      => $status,
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 立即执行一次
     */
    public function runOnce(int $jobId): void
    {
        $job = Db::name('sys_job')->where('job_id', $jobId)->find();
        if (!$job) {
            throw new \RuntimeException('job not found');
        }
        $this->executeJob($job, true);
    }

    /**
     * 执行到期且启用的任务（由 php think job:run 调用）
     * @param int|null $time Unix 时间戳，默认当前时间
     */
    public function runDue(?int $time = null): int
    {
        $time = $time ?? time();
        $jobs = Db::name('sys_job')->where('status', '0')->select()->toArray();
        $count = 0;
        foreach ($jobs as $job) {
            if (!QuartzCron::isDue((string) $job['cron_expression'], $time)) {
                continue;
            }
            $this->executeJob($job, false);
            $count++;
        }
        return $count;
    }

    /**
     * 校验 Cron 表达式字段数量是否合法
     */
    public function checkCronExpression(string $expression): bool
    {
        $parts = preg_split('/\s+/', trim($expression));
        return $parts !== false && count($parts) >= 5;
    }

    /**
     * 执行单个任务并写入调度日志
     * @param array $job 任务行数据
     * @param bool $manual 是否手动触发
     */
    public function executeJob(array $job, bool $manual): void
    {
        $jobId = (int) $job['job_id'];
        $concurrent = (string) ($job['concurrent'] ?? '1');
        $lockFile = runtime_path() . 'job' . DIRECTORY_SEPARATOR . 'job_' . $jobId . '.lock';
        $lockDir = dirname($lockFile);
        if (!is_dir($lockDir)) {
            mkdir($lockDir, 0755, true);
        }

        $fp = fopen($lockFile, 'c+');
        if ($fp === false) {
            throw new \RuntimeException('cannot open job lock');
        }
        if ($concurrent === '1' && !flock($fp, LOCK_EX | LOCK_NB)) {
            fclose($fp);
            if ($manual) {
                throw new \RuntimeException('job is running');
            }
            return;
        }

        $start = date('Y-m-d H:i:s');
        $status = '0';
        $message = 'success';
        $exception = '';
        try {
            $this->invokeTarget((string) $job['invoke_target']);
        } catch (\Throwable $e) {
            $status = '1';
            $message = 'failed';
            $exception = mb_substr($e->getMessage(), 0, 1900);
        } finally {
            $end = date('Y-m-d H:i:s');
            Db::name('sys_job_log')->insert([
                'job_name'       => $job['job_name'],
                'job_group'      => $job['job_group'],
                'invoke_target'  => $job['invoke_target'],
                'job_message'    => ($manual ? '[manual] ' : '') . $message,
                'status'         => $status,
                'exception_info' => $exception,
                'start_time'     => $start,
                'end_time'       => $end,
                'create_time'    => $end,
            ]);
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    /**
     * 按白名单调用 invoke_target（bean.method(args)）
     */
    public function invokeTarget(string $target): void
    {
        $target = trim($target);
        if ($target === '' || preg_match('/[;${}]/', $target)) {
            throw new \InvalidArgumentException('invalid invoke_target');
        }
        if (!preg_match('/^([a-zA-Z_][\w]*)\.([a-zA-Z_][\w]*)(?:\((.*)\))?$/', $target, $m)) {
            throw new \InvalidArgumentException('invoke_target format error');
        }
        $bean = $m[1];
        $method = $m[2];
        $argsRaw = $m[3] ?? '';
        if (!isset($this->whitelist[$bean])) {
            throw new \InvalidArgumentException('invoke target not in whitelist: ' . $bean);
        }
        $class = $this->whitelist[$bean];
        $obj = new $class();
        if (!method_exists($obj, $method)) {
            throw new \InvalidArgumentException('method not found: ' . $method);
        }
        $args = $this->parseArgs($argsRaw);
        $obj->{$method}(...$args);
    }

    /**
     * 校验调用目标是否在白名单
     */
    protected function assertInvokeAllowed(string $target): void
    {
        if ($target === '') {
            throw new \InvalidArgumentException('invoke_target required');
        }
        // 干跑解析校验
        if (!preg_match('/^([a-zA-Z_][\w]*)\./', $target, $m) || !isset($this->whitelist[$m[1]])) {
            throw new \InvalidArgumentException('invoke target not in whitelist');
        }
    }

    /**
     * 解析调用参数列表
     * @return list<mixed>
     */
    protected function parseArgs(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }
        $args = [];
        // 按逗号拆分，尊重引号
        $parts = preg_split('/,(?=(?:[^\'"]|\'[^\']*\'|"[^"]*")*$)/', $raw) ?: [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            if (preg_match("/^'(.*)'$/s", $part, $m) || preg_match('/^"(.*)"$/s', $part, $m)) {
                $args[] = $m[1];
                continue;
            }
            if (strcasecmp($part, 'true') === 0) {
                $args[] = true;
                continue;
            }
            if (strcasecmp($part, 'false') === 0) {
                $args[] = false;
                continue;
            }
            if (preg_match('/^-?\d+L$/i', $part)) {
                $args[] = (int) rtrim($part, 'Ll');
                continue;
            }
            if (preg_match('/^-?\d+(\.\d+)?D$/i', $part)) {
                $args[] = (float) rtrim($part, 'Dd');
                continue;
            }
            if (is_numeric($part)) {
                $args[] = str_contains($part, '.') ? (float) $part : (int) $part;
                continue;
            }
            $args[] = $part;
        }
        return $args;
    }
}
