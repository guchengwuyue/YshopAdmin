<?php
declare(strict_types=1);

namespace app\library\file\driver;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToCheckFileExistence;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;

/**
 * 本地磁盘驱动
 */
class LocalDriver implements StorageDriverInterface
{
    protected Filesystem $fs;
    protected string $root;
    protected string $domain;

    /**
     * @param array $config endpoint=根目录 domain=访问前缀
     */
    public function __construct(array $config)
    {
        $root = trim((string) ($config['endpoint'] ?? ''), " \t\n\r\0\x0B");
        if ($root === '') {
            $root = root_path() . 'public' . DIRECTORY_SEPARATOR . 'storage';
        } elseif (!preg_match('#^[a-zA-Z]:[\\\\/]|^/#', $root)) {
            $root = root_path() . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($root, '/\\'));
        }
        $this->root = rtrim($root, '/\\');
        if (!is_dir($this->root)) {
            mkdir($this->root, 0755, true);
        }
        $domain = trim((string) ($config['domain'] ?? ''));
        if ($domain === '') {
            $domain = '/storage';
        } elseif (preg_match('#^https?://[^/]+$#i', $domain)) {
            // 仅填主机时默认挂 /storage，与本地根目录 public/storage 对应
            $domain .= '/storage';
        }
        $this->domain = rtrim($domain, '/');
        $this->fs = new Filesystem(new LocalFilesystemAdapter($this->root));
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $path, mixed $contents): void
    {
        $path = $this->normalize($path);
        try {
            $this->fs->write($path, is_resource($contents) ? stream_get_contents($contents) : (string) $contents);
        } catch (UnableToWriteFile $e) {
            throw new \RuntimeException('本地写入失败: ' . $e->getMessage(), 0, $e);
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
            throw new \RuntimeException('本地删除失败: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function url(string $path): string
    {
        $path = $this->normalize($path);
        return $this->domain . '/' . ltrim(str_replace('\\', '/', $path), '/');
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
