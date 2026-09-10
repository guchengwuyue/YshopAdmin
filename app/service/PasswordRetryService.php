<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Cache;

/**
 * 密码重试锁定（对齐 sys.user.password.maxRetryCount）
 */
class PasswordRetryService
{
    public const CACHE_PREFIX = 'pwd_err_cnt:';

    /**
     * 获取最大重试次数
     */
    public function getMaxRetry(): int
    {
        $cfg = new ConfigService();
        $max = (int) $cfg->getKey('sys.user.password.maxRetryCount', '5');
        return max(1, $max);
    }

    /**
     * 判断账号是否已锁定
     */
    public function isLocked(string $loginName): bool
    {
        return $this->getCount($loginName) >= $this->getMaxRetry();
    }

    /**
     * 获取当前失败次数
     */
    public function getCount(string $loginName): int
    {
        return (int) Cache::get(self::CACHE_PREFIX . $loginName, 0);
    }

    /**
     * 记录一次密码错误
     */
    public function recordFail(string $loginName): void
    {
        $key = self::CACHE_PREFIX . $loginName;
        $cnt = $this->getCount($loginName) + 1;
        Cache::set($key, $cnt, 3600);
    }

    /**
     * 清除失败计数（登录成功后）
     */
    public function clear(string $loginName): void
    {
        Cache::delete(self::CACHE_PREFIX . $loginName);
    }
}
