<?php
declare(strict_types=1);

namespace app\library\file\driver;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToCheckFileExistence;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;

/**
 * S3 兼容对象存储驱动
 */
class S3Driver implements StorageDriverInterface
{
    protected Filesystem $fs;
    protected string $bucket;
    protected string $domain;
    protected S3Client $client;

    /**
     * @param array $config endpoint/bucket/access_key/access_secret/domain
     */
    public function __construct(array $config)
    {
        $endpoint = trim((string) ($config['endpoint'] ?? ''));
        $bucket = trim((string) ($config['bucket'] ?? ''));
        $key = trim((string) ($config['access_key'] ?? ''));
        $secret = (string) ($config['access_secret'] ?? '');
        if ($endpoint === '' || $bucket === '' || $key === '' || $secret === '') {
            throw new \InvalidArgumentException('S3 配置不完整（endpoint/bucket/accessKey/accessSecret）');
        }
        $this->bucket = $bucket;
        $domain = trim((string) ($config['domain'] ?? ''));
        $this->domain = rtrim($domain, '/');

        $clientConfig = [
            'version'                 => 'latest',
            'region'                  => (string) ($config['region'] ?? 'us-east-1'),
            'endpoint'                => $endpoint,
            'use_path_style_endpoint' => true,
            'credentials'             => [
                'key'    => $key,
                'secret' => $secret,
            ],
        ];
        $this->client = new S3Client($clientConfig);
        $adapter = new AwsS3V3Adapter($this->client, $this->bucket);
        $this->fs = new Filesystem($adapter);
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $path, mixed $contents): void
    {
        $path = $this->normalize($path);
        try {
            $body = is_resource($contents) ? stream_get_contents($contents) : (string) $contents;
            $this->fs->write($path, $body);
        } catch (UnableToWriteFile $e) {
            throw new \RuntimeException('S3 写入失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $path): void
    {
        $path = $this->normalize($path);
        try {
            if ($this->fs->fileExists($path)) {
                $this->fs->delete($path);
            }
        } catch (UnableToDeleteFile $e) {
            throw new \RuntimeException('S3 删除失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function url(string $path): string
    {
        $path = $this->normalize($path);
        if ($this->domain !== '') {
            return $this->domain . '/' . ltrim($path, '/');
        }
        return $this->client->getObjectUrl($this->bucket, $path);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $path): bool
    {
        $path = $this->normalize($path);
        try {
            return $this->fs->fileExists($path);
        } catch (UnableToCheckFileExistence) {
            return false;
        }
    }

    /**
     * 规范化相对路径
     */
    protected function normalize(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        if ($path === '' || str_contains($path, '..')) {
            throw new \InvalidArgumentException('非法存储路径');
        }
        return $path;
    }
}
