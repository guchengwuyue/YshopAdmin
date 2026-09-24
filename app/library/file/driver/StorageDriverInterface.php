<?php
declare(strict_types=1);

namespace app\library\file\driver;

/**
 * 存储驱动接口
 */
interface StorageDriverInterface
{
    /**
     * 写入对象
     * @param string $path 相对路径
     * @param mixed $contents 字符串或资源流
     */
    public function put(string $path, mixed $contents): void;

    /**
     * 删除对象
     */
    public function delete(string $path): void;

    /**
     * 公开访问 URL
     */
    public function url(string $path): string;

    /**
     * 是否存在
     */
    public function exists(string $path): bool;
}
