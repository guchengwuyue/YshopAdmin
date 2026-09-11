<?php
declare(strict_types=1);

use app\common\addon\AddonManager;

if (!function_exists('addon_path')) {
    /**
     * Addon root path (trailing separator)
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
     * Absolute addon view path for View::fetch (do not change view_path)
     * @param string $name addon name
     * @param string $template relative template e.g. config/index or api/wechat
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
     * Read addon info.ini
     */
    function get_addon_info(string $name): array
    {
        return AddonManager::instance()->getInfo($name);
    }
}

if (!function_exists('get_addon_config')) {
    /**
     * Read flat addon config
     */
    function get_addon_config(string $name): array
    {
        return AddonManager::instance()->getConfig($name);
    }
}

if (!function_exists('set_addon_info')) {
    /**
     * Write addon info.ini
     */
    function set_addon_info(string $name, array $info): bool
    {
        return AddonManager::instance()->setInfo($name, $info);
    }
}

if (!function_exists('set_addon_config')) {
    /**
     * Write addon config values
     */
    function set_addon_config(string $name, array $config): bool
    {
        return AddonManager::instance()->setConfig($name, $config);
    }
}

if (!function_exists('addon_url')) {
    /**
     * Build addon public URL as a literal path (not ThinkPHP controller url())
     * @param string $url e.g. yspay/api/notifyx or addons/yspay/api/wechat
     */
    function addon_url(string $url, array $vars = [], bool $suffix = false, $domain = false): string
    {
        $url = ltrim(str_replace('.', '/', $url), '/');
        if (!str_starts_with($url, 'addons/')) {
            $url = 'addons/' . $url;
        }
        // Do not call url(): ThinkPHP treats "addons/..." as controller path
        // and generates "/yspay/..." which auto-routes to app\controller\Yspay.
        $path = '/' . $url;
        if ($vars !== []) {
            $path .= (str_contains($path, '?') ? '&' : '?') . http_build_query($vars);
        }
        if ($domain === true || $domain === 'auto') {
            try {
                $req = request();
                $root = rtrim((string) $req->domain() . (string) $req->root(), '/');
            } catch (\Throwable) {
                $root = '';
            }
            if ($root === '' || !preg_match('#^https?://#i', $root)) {
                return $path;
            }
            return $root . $path;
        }
        if (is_string($domain) && $domain !== '') {
            return rtrim($domain, '/') . $path;
        }
        return $path;
    }
}

if (!function_exists('hook')) {
    /**
     * Trigger addon event via Think Event
     */
    function hook(string $event, mixed $params = null): mixed
    {
        return AddonManager::instance()->hook($event, $params);
    }
}
