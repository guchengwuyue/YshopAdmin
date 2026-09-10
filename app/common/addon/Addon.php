<?php
declare(strict_types=1);

namespace app\common\addon;

/**
 * 插件基类
 */
abstract class Addon
{
    /** @var string 插件标识 */
    protected string $name = '';

    /** @var array info.ini 缓存 */
    protected array $info = [];

    public function __construct(string $name = '')
    {
        if ($name !== '') {
            $this->name = $name;
        }
        if ($this->name === '') {
            $class = static::class;
            $parts = explode('\\', $class);
            $this->name = strtolower($parts[1] ?? '');
        }
    }

    /**
     * 安装
     */
    abstract public function install(): bool;

    /**
     * 卸载
     */
    abstract public function uninstall(): bool;

    /**
     * 启用
     */
    public function enable(): bool
    {
        return true;
    }

    /**
     * 禁用
     */
    public function disable(): bool
    {
        return true;
    }

    /**
     * 插件标识
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 读取 info.ini
     */
    public function getInfo(): array
    {
        if ($this->info) {
            return $this->info;
        }
        $this->info = AddonManager::instance()->getInfo($this->name);
        return $this->info;
    }

    /**
     * 读取扁平配置
     */
    public function getConfig(): array
    {
        return AddonManager::instance()->getConfig($this->name);
    }
}
