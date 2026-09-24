<?php
declare(strict_types=1);

namespace app\common;

use think\facade\Cache;
use think\Request;

/**
 * 管理员 / 会员 Bearer Token（前缀隔离，缓存存储）
 */
class Token
{
    public const REALM_ADMIN = 'admin';
    public const REALM_MEMBER = 'member';
    public const PREFIX_ADMIN = 'adm_';
    public const PREFIX_MEMBER = 'mem_';

    /**
     * 签发 Token
     * @param array<string, mixed> $subject
     * @return array{token: string, tokenType: string, expiresIn: int}
     */
    public static function issue(string $realm, array $subject, ?int $ttl = null): array
    {
        $ttl = $ttl ?? self::ttl($realm);
        $token = self::prefix($realm) . bin2hex(random_bytes(24));
        Cache::set(self::cacheKey($token), [
            'realm'   => $realm,
            'ttl'     => $ttl,
            'subject' => $subject,
        ], $ttl);

        return [
            'token'     => $token,
            'tokenType' => 'Bearer',
            'expiresIn' => $ttl,
        ];
    }

    /**
     * 读取并滑动续期；无效或 realm 不匹配返回 null
     * @return array<string, mixed>|null
     */
    public static function read(string $token, ?string $expectRealm = null): ?array
    {
        $token = trim($token);
        if ($token === '' || self::realmOf($token) === null) {
            return null;
        }
        $row = Cache::get(self::cacheKey($token));
        if (!is_array($row) || !isset($row['subject']) || !is_array($row['subject'])) {
            return null;
        }
        $realm = (string) ($row['realm'] ?? '');
        if ($realm === '' || !str_starts_with($token, self::prefix($realm))) {
            return null;
        }
        if ($expectRealm !== null && $realm !== $expectRealm) {
            return null;
        }
        $ttl = (int) ($row['ttl'] ?? self::ttl($realm));
        Cache::set(self::cacheKey($token), $row, $ttl);
        $subject = $row['subject'];
        $subject['realm'] = $realm;
        return $subject;
    }

    public static function revoke(string $token): void
    {
        $token = trim($token);
        if ($token === '') {
            return;
        }
        Cache::delete(self::cacheKey($token));
    }

    public static function bearer(Request $request): string
    {
        $header = (string) $request->header('authorization', '');
        if (preg_match('/^\s*Bearer\s+(\S+)\s*$/i', $header, $m)) {
            return $m[1];
        }
        return '';
    }

    public static function realmOf(string $token): ?string
    {
        if (str_starts_with($token, self::PREFIX_ADMIN)) {
            return self::REALM_ADMIN;
        }
        if (str_starts_with($token, self::PREFIX_MEMBER)) {
            return self::REALM_MEMBER;
        }
        return null;
    }

    public static function ttl(string $realm): int
    {
        if ($realm === self::REALM_MEMBER) {
            return max(60, (int) config('token.member_ttl', 604800));
        }
        return max(60, (int) config('token.admin_ttl', 7200));
    }

    protected static function prefix(string $realm): string
    {
        return $realm === self::REALM_MEMBER ? self::PREFIX_MEMBER : self::PREFIX_ADMIN;
    }

    protected static function cacheKey(string $token): string
    {
        return 'api_token:' . hash('sha256', $token);
    }
}
