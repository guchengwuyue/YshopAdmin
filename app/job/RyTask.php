<?php
declare(strict_types=1);

namespace app\job;
use think\facade\Log;

/**
 * Whitelisted sample job targets (RuoYi ryTask.*).
 */
class RyTask
{
    public function ryNoParams(): void
    {
        // no-op sample
        //打印一个日志测试
        Log::info('yshop test');
    }

    public function ryParams(string $param = ''): void
    {
        // sample with one string param
    }

    public function ryMultipleParams(string $s = '', bool $b = false, int $l = 0, float $d = 0.0, int $i = 0): void
    {
        // sample with multiple params
    }
}
