<?php
declare(strict_types=1);

use app\common\addon\AddonManager;

if (!function_exists('addon_path')) {
    /**
     * 插件根目录（末尾带分隔符）
     */
    function addon_path(string $name = ''): string
    {
        if (!defined('ADDON_PATH')) {
            define('ADDON_PATH', root_path() . 'addons' . DIRECTORY_SEPARATOR);
        }
        return $name === '' ? ADDON_PATH : ADDON_PATH . $name . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('addon_view')) {
    /**
     * 插件视图绝对路径（带 .html，供 View::fetch 使用，不改 view_path）
     * @param string $name 插件标识
     * @param string $template 相对路径，如 addons/addons 或 api/wechat
     */
    function addon_view(string $name, string $template): string
    {
        $template = str_replace(['/', ':'], DIRECTORY_SEPARATOR, ltrim($template, '/\\'));
        if (!str_ends_with(strtolower($template), '.html')) {
            $template .= '.html';
        }
        return addon_path($name) . 'view' . DIRECTORY_SEPARATOR . $template;
    }
}

if (!function_exists('get_addon_info')) {
    /**
     * 读取插件 info.ini
     */
    function get_addon_info(string $name): array
    {
        return AddonManager::instance()->getInfo($name);
    }
}

if (!function_exists('get_addon_config')) {
    /**
     * 读取插件扁平配置
     */
    function get_addon_config(string $name): array
    {
        return AddonManager::instance()->getConfig($name);
    }
}

if (!function_exists('set_addon_info')) {
    /**
     * 写入插件 info.ini
     */
    function set_addon_info(string $name, array $info): bool
    {
        return AddonManager::instance()->setInfo($name, $info);
    }
}

if (!function_exists('set_addon_config')) {
    /**
     * 写入插件配置值
     */
    function set_addon_config(string $name, array $config): bool
    {
        return AddonManager::instance()->setConfig($name, $config);
    }
}

if (!function_exists('addon_url')) {
    /**
     * 生成插件 URL
     * @param string $url 形如 yspay/api/notifyx
     */
    function addon_url(string $url, array $vars = [], bool $suffix = true, $domain = false): string
    {
        $url = ltrim(str_replace('.', '/', $url), '/');
        if (!str_starts_with($url, 'addons/')) {
            $url = 'addons/' . $url;
        }
        return (string) url($url, $vars, $suffix, $domain);
    }
}

if (!function_exists('hook')) {
    /**
     * 触发插件事件（桥接 Think Event）
     */
    function hook(string $event, mixed $params = null): mixed
    {
        return AddonManager::instance()->hook($event, $params);
    }
}
