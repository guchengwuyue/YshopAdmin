<?php
declare(strict_types=1);

namespace app\common;

/**
 * 当前请求的会员身份（仅 Bearer mem_，不写 Session）
 */
class MemberContext
{
    protected static bool $bound = false;

    /** @var array<string, mixed>|null */
    protected static ?array $member = null;

    /**
     * @param array<string, mixed> $member
     */
    public static function bind(array $member): void
    {
        self::$bound = true;
        self::$member = $member;
    }

    public static function clear(): void
    {
        self::$bound = false;
        self::$member = null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function user(): ?array
    {
        return self::$bound ? self::$member : null;
    }

    public static function id(): int
    {
        $user = self::user();
        return $user ? (int) ($user['id'] ?? 0) : 0;
    }
}
