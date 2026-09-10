<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Cache;
use think\facade\Db;

/**
 * 缓存监控（配置 / 字典 / 密码重试等命名缓存）
 */
class CacheMonitorService
{
    public const NAME_CONFIG = 'sys-config';
    public const NAME_DICT = 'sys-dict';
    public const NAME_PWD = 'pwd-err-cnt';

    /**
     * 缓存名称列表
     * @return list<string>
     */
    public function getCacheNames(): array
    {
        return [self::NAME_CONFIG, self::NAME_DICT, self::NAME_PWD];
    }

    /**
     * 指定缓存名称下的键列表
     * @return list<string>
     */
    public function getKeys(string $cacheName): array
    {
        if ($cacheName === self::NAME_CONFIG) {
            $keys = Db::name('sys_config')->column('config_key');
            return array_map(static fn($k) => self::NAME_CONFIG . ':' . $k, $keys);
        }
        if ($cacheName === self::NAME_DICT) {
            $keys = Db::name('sys_dict_type')->column('dict_type');
            return array_map(static fn($k) => self::NAME_DICT . ':' . $k, $keys);
        }
        if ($cacheName === self::NAME_PWD) {
            return $this->scanFileKeys(PasswordRetryService::CACHE_PREFIX);
        }
        return [];
    }

    /**
     * 读取缓存键值
     */
    public function getValue(string $cacheName, string $cacheKey): mixed
    {
        $key = $this->normalizeKey($cacheName, $cacheKey);
        return Cache::get($key);
    }

    /**
     * 清空指定缓存名称下全部键
     */
    public function clearCacheName(string $cacheName): void
    {
        foreach ($this->getKeys($cacheName) as $fullKey) {
            Cache::delete($fullKey);
        }
        // 同时清理标签缓存
        try {
            Cache::tag($cacheName)->clear();
        } catch (\Throwable $e) {
        }
    }

    /**
     * 清空指定缓存键
     */
    public function clearCacheKey(string $cacheName, string $cacheKey): void
    {
        Cache::delete($this->normalizeKey($cacheName, $cacheKey));
    }

    /**
     * 清空全部命名缓存
     */
    public function clearAll(): void
    {
        foreach ($this->getCacheNames() as $name) {
            $this->clearCacheName($name);
        }
        try {
            Cache::clear();
        } catch (\Throwable $e) {
        }
    }

    /**
     * 预热配置缓存
     */
    public function warmConfig(string $configKey, string $value): void
    {
        Cache::tag(self::NAME_CONFIG)->set(self::NAME_CONFIG . ':' . $configKey, $value);
    }

    /**
     * 预热字典缓存
     */
    public function warmDict(string $dictType, array $rows): void
    {
        Cache::tag(self::NAME_DICT)->set(self::NAME_DICT . ':' . $dictType, $rows);
    }

    /**
     * 读取配置（优先缓存，未命中回源并预热）
     */
    public function getConfigCached(string $configKey): ?string
    {
        $key = self::NAME_CONFIG . ':' . $configKey;
        $val = Cache::get($key);
        if ($val !== null && $val !== false) {
            return (string) $val;
        }
        $dbVal = Db::name('sys_config')->where('config_key', $configKey)->value('config_value');
        if ($dbVal === null) {
            return null;
        }
        $this->warmConfig($configKey, (string) $dbVal);
        return (string) $dbVal;
    }

    /**
     * 读取字典数据（优先缓存，未命中回源并预热）
     */
    public function getDictCached(string $dictType): array
    {
        $key = self::NAME_DICT . ':' . $dictType;
        $val = Cache::get($key);
        if (is_array($val)) {
            return $val;
        }
        $rows = Db::name('sys_dict_data')->where('dict_type', $dictType)->where('status', '0')
            ->order('dict_sort')->select()->toArray();
        $this->warmDict($dictType, $rows);
        return $rows;
    }

    /**
     * 规范化缓存键（补全缓存名称前缀）
     */
    protected function normalizeKey(string $cacheName, string $cacheKey): string
    {
        if (str_starts_with($cacheKey, $cacheName . ':')) {
            return $cacheKey;
        }
        return $cacheName . ':' . $cacheKey;
    }

    /**
     * 扫描文件缓存中带指定前缀的键
     * @return list<string>
     */
    protected function scanFileKeys(string $prefix): array
    {
        $path = runtime_path() . 'cache';
        if (!is_dir($path)) {
            return [];
        }
        $keys = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $content = @file_get_contents($file->getPathname());
            if ($content === false) {
                continue;
            }
            // ThinkPHP 文件缓存载荷中尽力扫描前缀键
            if (str_contains($content, $prefix)) {
                if (preg_match_all('/' . preg_quote($prefix, '/') . '([a-zA-Z0-9_\-]+)/', $content, $m)) {
                    foreach ($m[0] as $full) {
                        $keys[$full] = $full;
                    }
                }
            }
        }
        return array_values($keys);
    }
}
