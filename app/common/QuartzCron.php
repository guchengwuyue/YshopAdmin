<?php
declare(strict_types=1);

namespace app\common;

/**
 * Quartz 风格 Cron 表达式（秒 分 时 日 月 周）
 */
class QuartzCron
{
    /**
     * 判断表达式在指定时间是否到期（按分钟粒度，忽略秒）
     * @param string $expression Cron 表达式
     * @param int|null $time Unix 时间戳，默认当前时间
     */
    public static function isDue(string $expression, ?int $time = null): bool
    {
        $time = $time ?? time();
        $parts = preg_split('/\s+/', trim($expression));
        if (!$parts || count($parts) < 5) {
            return false;
        }
        // Quartz 6/7 字段 vs Unix 5 字段
        if (count($parts) >= 6) {
            // 秒 分 时 日 月 周 [年]
            [, $min, $hour, $day, $month, $week] = array_pad($parts, 6, '*');
        } else {
            [$min, $hour, $day, $month, $week] = array_pad($parts, 5, '*');
        }

        $values = [
            (int) date('i', $time),
            (int) date('G', $time),
            (int) date('j', $time),
            (int) date('n', $time),
            (int) date('w', $time), // 0=周日
        ];
        $fields = [$min, $hour, $day, $month, $week];

        foreach ($fields as $i => $field) {
            if (!self::matchField($field, $values[$i], $i === 4)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 匹配单个 Cron 字段
     * @param bool $isWeek 是否为星期字段
     */
    protected static function matchField(string $field, int $value, bool $isWeek): bool
    {
        $field = trim($field);
        if ($field === '*' || $field === '?') {
            return true;
        }
        foreach (explode(',', $field) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            if (str_contains($part, '/')) {
                [$range, $step] = explode('/', $part, 2);
                $step = max(1, (int) $step);
                if ($range === '*' || $range === '?') {
                    if ($value % $step === 0) {
                        return true;
                    }
                    continue;
                }
                if (str_contains($range, '-')) {
                    [$a, $b] = array_map('intval', explode('-', $range, 2));
                    if ($value >= $a && $value <= $b && (($value - $a) % $step === 0)) {
                        return true;
                    }
                    continue;
                }
                $base = (int) $range;
                if ($value >= $base && (($value - $base) % $step === 0)) {
                    return true;
                }
                continue;
            }
            if (str_contains($part, '-')) {
                [$a, $b] = array_map('intval', explode('-', $part, 2));
                if ($value >= $a && $value <= $b) {
                    return true;
                }
                continue;
            }
            // Quartz 周日可为 1 或 7；PHP date('w') 为 0-6
            if ($isWeek && ((int) $part === 7 || (int) $part === 1) && ($value === 0 || $value === 1)) {
                // value 为 0 时将 1/7 宽松视为周日
                if (((int) $part === 7 || (int) $part === 1) && $value === 0) {
                    return true;
                }
            }
            if ((int) $part === $value) {
                return true;
            }
        }
        return false;
    }
}
