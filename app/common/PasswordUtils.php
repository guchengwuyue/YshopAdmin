<?php
declare(strict_types=1);

namespace app\common;

/**
 * RuoYi 经典密码：md5(loginName + password + salt)
 */
class PasswordUtils
{
    /**
     * 加密密码
     */
    public static function encryptPassword(string $loginName, string $password, string $salt): string
    {
        return md5($loginName . $password . $salt);
    }

    /**
     * 校验明文密码是否匹配
     * @param string $rawPassword 明文密码
     * @param string $encodedPassword 库中密文
     */
    public static function matches(string $loginName, string $rawPassword, string $salt, string $encodedPassword): bool
    {
        return hash_equals(strtolower($encodedPassword), strtolower(self::encryptPassword($loginName, $rawPassword, $salt)));
    }

    /**
     * 生成 6 位十六进制盐（3 随机字节）
     */
    public static function randomSalt(): string
    {
        return bin2hex(random_bytes(3));
    }
}
