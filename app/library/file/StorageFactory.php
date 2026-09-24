<?php
declare(strict_types=1);

namespace app\library\file;

use app\library\file\driver\LocalDriver;
use app\library\file\driver\S3Driver;
use app\library\file\driver\StorageDriverInterface;

/**
 * 按配置创建存储驱动
 */
class StorageFactory
{
    /**
     * @param array $config 含 storage 及驱动字段（snake 或 camel）
     */
    public static function make(array $config): StorageDriverInterface
    {
        $storage = strtolower((string) ($config['storage'] ?? 'local'));
        $normalized = [
            'endpoint'      => (string) ($config['endpoint'] ?? ''),
            'bucket'        => (string) ($config['bucket'] ?? ''),
            'access_key'    => (string) ($config['access_key'] ?? $config['accessKey'] ?? ''),
            'access_secret' => (string) ($config['access_secret'] ?? $config['accessSecret'] ?? ''),
            'domain'        => (string) ($config['domain'] ?? ''),
            'region'        => (string) ($config['region'] ?? 'us-east-1'),
        ];

        return match ($storage) {
            'local' => new LocalDriver($normalized),
            's3'    => new S3Driver($normalized),
            default => throw new \InvalidArgumentException('不支持的存储器: ' . $storage),
        };
    }
}
