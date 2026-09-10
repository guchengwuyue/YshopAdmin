<?php
declare(strict_types=1);

namespace app\common;

/**
 * User-Agent 简易解析（浏览器 / 操作系统）
 */
class UserAgent
{
    /**
     * 解析浏览器名称
     * @param string|null $ua User-Agent，默认取当前请求
     */
    public static function browser(?string $ua = null): string
    {
        $ua = $ua ?? (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        if ($ua === '') {
            return 'Unknown';
        }
        $map = [
            'Edg' => 'Edge',
            'Edge' => 'Edge',
            'Chrome' => 'Chrome',
            'Safari' => 'Safari',
            'Firefox' => 'Firefox',
            'MSIE' => 'IE',
            'Trident' => 'IE',
            'Opera' => 'Opera',
        ];
        foreach ($map as $needle => $name) {
            if (stripos($ua, $needle) !== false) {
                return $name;
            }
        }
        return 'Unknown';
    }

    /**
     * 解析操作系统名称
     * @param string|null $ua User-Agent，默认取当前请求
     */
    public static function os(?string $ua = null): string
    {
        $ua = $ua ?? (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        if ($ua === '') {
            return 'Unknown';
        }
        $map = [
            'Windows NT 10' => 'Windows 10',
            'Windows NT 11' => 'Windows 11',
            'Windows NT 6.3' => 'Windows 8.1',
            'Windows NT 6.2' => 'Windows 8',
            'Windows NT 6.1' => 'Windows 7',
            'Mac OS X' => 'Mac OS X',
            'Android' => 'Android',
            'iPhone' => 'iOS',
            'iPad' => 'iOS',
            'Linux' => 'Linux',
        ];
        foreach ($map as $needle => $name) {
            if (stripos($ua, $needle) !== false) {
                return $name;
            }
        }
        return 'Unknown';
    }
}
