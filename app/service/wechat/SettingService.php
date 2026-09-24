<?php
declare(strict_types=1);

namespace app\service\wechat;

use app\common\BuiltinConfig;

/**
 * 公众号凭证（系统内置）
 */
class SettingService
{
    /**
     * @return array{config:array,serverUrl:string}
     */
    public function load(string $domain): array
    {
        return [
            'config'    => BuiltinConfig::get('yswechat'),
            'serverUrl' => rtrim($domain, '/') . '/api/wechat/server',
        ];
    }

    public function save(array $post): void
    {
        $config = BuiltinConfig::get('yswechat');
        foreach (['app_id', 'secret', 'token', 'aes_key', 'debug', 'log_level', 'oauth_callback'] as $key) {
            if (array_key_exists($key, $post)) {
                $config[$key] = is_array($post[$key]) ? $post[$key] : (string) $post[$key];
            }
        }
        BuiltinConfig::set('yswechat', $config);
    }
}
