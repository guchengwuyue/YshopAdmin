<?php
declare(strict_types=1);

namespace app\service;

/**
 * 服务监控（CPU / 内存 / 磁盘 / PHP / 系统信息）
 */
class ServerService
{
    /**
     * 采集服务监控数据
     */
    public function collect(): array
    {
        $mem = $this->memory();
        $php = $this->phpInfo();
        return [
            'cpu'  => $this->cpu(),
            'mem'  => $mem,
            'jvm'  => $php, // 复用 JVM 列展示 PHP 进程内存
            'sys'  => $this->sys(),
            'sysFiles' => $this->disks(),
        ];
    }

    /**
     * CPU 使用信息
     */
    protected function cpu(): array
    {
        $cores = (int) (getenv('NUMBER_OF_PROCESSORS') ?: 1);
        if (is_file('/proc/cpuinfo')) {
            $cores = max(1, substr_count((string) file_get_contents('/proc/cpuinfo'), 'processor'));
        }
        $load = [0.0, 0.0, 0.0];
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg() ?: $load;
        } elseif (PHP_OS_FAMILY === 'Windows') {
            $load[0] = $this->windowsCpu();
        }
        $used = round(min(100, (float) $load[0] * (100 / max(1, $cores))), 2);
        $sys = round($used * 0.4, 2);
        $user = round($used * 0.6, 2);
        return [
            'cpuNum' => $cores,
            'used'   => $user,
            'sys'    => $sys,
            'free'   => round(max(0, 100 - $used), 2),
        ];
    }

    /**
     * Windows CPU 负载
     */
    protected function windowsCpu(): float
    {
        try {
            $out = [];
            @exec('wmic cpu get loadpercentage /value', $out);
            foreach ($out as $line) {
                if (stripos($line, 'LoadPercentage') !== false) {
                    return (float) preg_replace('/\D/', '', $line) / max(1, (int) (getenv('NUMBER_OF_PROCESSORS') ?: 1));
                }
            }
        } catch (\Throwable $e) {
        }
        return 0.0;
    }

    /**
     * 系统内存信息
     */
    protected function memory(): array
    {
        $total = 0.0;
        $free = 0.0;
        if (PHP_OS_FAMILY === 'Windows') {
            $out = [];
            @exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /value', $out);
            $map = [];
            foreach ($out as $line) {
                if (str_contains($line, '=')) {
                    [$k, $v] = explode('=', $line, 2);
                    $map[trim($k)] = (float) trim($v);
                }
            }
            $total = ($map['TotalVisibleMemorySize'] ?? 0) / 1024 / 1024;
            $free = ($map['FreePhysicalMemory'] ?? 0) / 1024 / 1024;
        } elseif (is_file('/proc/meminfo')) {
            $info = file_get_contents('/proc/meminfo');
            if (preg_match('/MemTotal:\s+(\d+)/', (string) $info, $m)) {
                $total = ((float) $m[1]) / 1024 / 1024;
            }
            if (preg_match('/MemAvailable:\s+(\d+)/', (string) $info, $m)) {
                $free = ((float) $m[1]) / 1024 / 1024;
            }
        }
        $used = max(0, $total - $free);
        $usage = $total > 0 ? round($used / $total * 100, 2) : 0;
        return [
            'total' => round($total, 2),
            'used'  => round($used, 2),
            'free'  => round($free, 2),
            'usage' => $usage,
        ];
    }

    /**
     * PHP 进程内存与运行信息
     */
    protected function phpInfo(): array
    {
        $limit = $this->parseBytes((string) ini_get('memory_limit'));
        $used = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $total = $limit > 0 ? $limit : max($peak, $used);
        $free = max(0, $total - $used);
        $usage = $total > 0 ? round($used / $total * 100, 2) : 0;
        return [
            'total'   => round($total / 1024 / 1024, 2),
            'used'    => round($used / 1024 / 1024, 2),
            'free'    => round($free / 1024 / 1024, 2),
            'usage'   => $usage,
            'name'    => 'PHP ' . PHP_VERSION,
            'home'    => PHP_BINARY,
            'startTime' => defined('START_TIME') ? date('Y-m-d H:i:s', (int) START_TIME) : date('Y-m-d H:i:s'),
            'runTime' => $this->uptime(),
            'inputArgs'=> implode(' ', $_SERVER['argv'] ?? []),
            'version' => PHP_VERSION,
        ];
    }

    /**
     * 操作系统信息
     */
    protected function sys(): array
    {
        return [
            'computerName' => php_uname('n'),
            'computerIp'   => $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname() ?: 'localhost'),
            'osName'       => php_uname('s') . ' ' . php_uname('r'),
            'osArch'       => php_uname('m'),
            'userDir'      => getcwd() ?: '',
        ];
    }

    /**
     * 磁盘分区信息
     */
    protected function disks(): array
    {
        $list = [];
        if (PHP_OS_FAMILY === 'Windows') {
            foreach (range('C', 'Z') as $letter) {
                $root = $letter . ':\\';
                if (!is_dir($root)) {
                    continue;
                }
                $total = @disk_total_space($root);
                $free = @disk_free_space($root);
                if ($total === false) {
                    continue;
                }
                $used = $total - (float) $free;
                $list[] = [
                    'dirName'     => $root,
                    'sysTypeName' => 'NTFS',
                    'typeName'    => 'Local',
                    'total'       => $this->formatSize((float) $total),
                    'free'        => $this->formatSize((float) $free),
                    'used'        => $this->formatSize($used),
                    'usage'       => $total > 0 ? round($used / $total * 100, 2) : 0,
                ];
            }
        } else {
            $total = @disk_total_space('/');
            $free = @disk_free_space('/');
            $used = (float) $total - (float) $free;
            $list[] = [
                'dirName'     => '/',
                'sysTypeName' => 'ext4',
                'typeName'    => 'Local',
                'total'       => $this->formatSize((float) $total),
                'free'        => $this->formatSize((float) $free),
                'used'        => $this->formatSize($used),
                'usage'       => $total > 0 ? round($used / $total * 100, 2) : 0,
            ];
        }
        return $list;
    }

    /**
     * 解析 php.ini 内存单位为字节
     */
    protected function parseBytes(string $val): int
    {
        $val = trim($val);
        if ($val === '' || $val === '-1') {
            return 0;
        }
        $unit = strtolower(substr($val, -1));
        $num = (float) $val;
        return (int) match ($unit) {
            'g' => $num * 1024 * 1024 * 1024,
            'm' => $num * 1024 * 1024,
            'k' => $num * 1024,
            default => (int) $num,
        };
    }

    /**
     * 格式化字节大小
     */
    protected function formatSize(float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * 系统运行时长
     */
    protected function uptime(): string
    {
        if (is_file('/proc/uptime')) {
            $sec = (float) explode(' ', (string) file_get_contents('/proc/uptime'))[0];
            return $this->formatDuration((int) $sec);
        }
        return '-';
    }

    /**
     * 格式化秒数为可读时长
     */
    protected function formatDuration(int $sec): string
    {
        $d = intdiv($sec, 86400);
        $h = intdiv($sec % 86400, 3600);
        $m = intdiv($sec % 3600, 60);
        $s = $sec % 60;
        return sprintf('%d天%d时%d分%d秒', $d, $h, $m, $s);
    }
}
