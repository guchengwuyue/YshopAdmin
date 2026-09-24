<?php
declare(strict_types=1);

namespace app\common;

/**
 * Dual entry (front / admin) helpers.
 */
class Entry
{
    public const FRONT = 'front';
    public const ADMIN = 'admin';
    public const ADMIN_ROOT = '/yshopadmin';

    public static function name(): string
    {
        if (defined('YSHOP_ENTRY')) {
            return (string) YSHOP_ENTRY;
        }
        return self::FRONT;
    }

    public static function isAdmin(): bool
    {
        return self::name() === self::ADMIN;
    }

    public static function isFront(): bool
    {
        return !self::isAdmin();
    }

    /**
     * Admin URL prefix (no trailing slash), e.g. /yshopadmin
     */
    public static function adminRoot(): string
    {
        return self::ADMIN_ROOT;
    }

    /**
     * Build admin path without ThinkPHP url() / .html suffix / multi-app baseFile quirks.
     * e.g. Entry::url('/login') => /yshopadmin/login
     */
    public static function url(string $path = ''): string
    {
        $path = trim(str_replace('\\', '/', $path), '/');
        if ($path === '') {
            return self::ADMIN_ROOT;
        }
        return self::ADMIN_ROOT . '/' . $path;
    }

    /**
     * Front/site URL root — never /yshopadmin.
     * Public addon paths (/addons/...) and payment callbacks use this origin.
     * e.g. publicRoot(true) => http://example.com
     */
    public static function publicRoot(bool $complete = false): string
    {
        try {
            $req = request();
        } catch (\Throwable) {
            return '';
        }
        if (self::isAdmin()) {
            return $complete ? rtrim((string) $req->domain(), '/') : '';
        }
        return $req->root($complete);
    }
}
