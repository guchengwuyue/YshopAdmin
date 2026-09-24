<?php
declare(strict_types=1);

namespace app\library\wechat\response;

/**
 * 回复处理器注册表
 */
class HandlerRegistry
{
    /** @var array<string, ResponseHandlerInterface> */
    protected static array $handlers = [];

    protected static bool $booted = false;

    /**
     * 注册内置与扩展目录处理器
     */
    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }
        self::$booted = true;
        self::register('text', new TextHandler());

        $dir = __DIR__;
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) ?: [] as $file) {
            if (!preg_match('/^([A-Za-z][A-Za-z0-9]*)Handler\.php$/', $file, $m)) {
                continue;
            }
            $key = strtolower($m[1]);
            if ($key === 'text' || isset(self::$handlers[$key])) {
                continue;
            }
            $class = __NAMESPACE__ . '\\' . $m[1] . 'Handler';
            if (class_exists($class)) {
                $obj = new $class();
                if ($obj instanceof ResponseHandlerInterface) {
                    self::register($key, $obj);
                }
            }
        }
    }

    /**
     * 注册处理器
     */
    public static function register(string $name, ResponseHandlerInterface $handler): void
    {
        self::$handlers[strtolower($name)] = $handler;
    }

    /**
     * 获取处理器
     */
    public static function get(string $name): ?ResponseHandlerInterface
    {
        self::boot();
        return self::$handlers[strtolower($name)] ?? null;
    }

    /**
     * 全部处理器配置（后台选择 app 类型用）
     * @return array<string, array>
     */
    public static function appConfig(): array
    {
        self::boot();
        $list = [];
        foreach (self::$handlers as $key => $handler) {
            if ($key === 'text') {
                continue;
            }
            $cfg = $handler->config();
            $cfg['available'] = $handler->available();
            $cfg['name'] = ($cfg['name'] ?? $key) . ($cfg['available'] ? '' : '(未安装或未启用)');
            $list[$key] = $cfg;
        }
        return $list;
    }
}
