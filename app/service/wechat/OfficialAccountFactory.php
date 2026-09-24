<?php
declare(strict_types=1);

namespace app\service\wechat;

use EasyWeChat\Factory;
use EasyWeChat\OfficialAccount\Application;

/**
 * EasyWeChat OfficialAccount 工厂
 */
class OfficialAccountFactory
{
    /**
     * 构建公众号应用实例
     */
    public static function make(): Application
    {
        return Factory::officialAccount(self::loadConfig());
    }

    /**
     * 从插件配置组装 EasyWeChat 配置
     */
    public static function loadConfig(): array
    {
        $config = \app\common\BuiltinConfig::get('yswechat');
        $debug = (string) ($config['debug'] ?? '0') === '1';
        $level = (string) ($config['log_level'] ?? 'info');
        $logPath = runtime_path() . 'log' . DIRECTORY_SEPARATOR . 'easywechat.log';

        return [
            'app_id'        => (string) ($config['app_id'] ?? ''),
            'secret'        => (string) ($config['secret'] ?? ''),
            'token'         => (string) ($config['token'] ?? ''),
            'aes_key'       => (string) ($config['aes_key'] ?? ''),
            'response_type' => 'array',
            'log'           => [
                'default'  => $debug ? 'dev' : 'prod',
                'channels' => [
                    'dev'  => [
                        'driver' => 'single',
                        'path'   => $logPath,
                        'level'  => 'debug',
                    ],
                    'prod' => [
                        'driver' => 'daily',
                        'path'   => $logPath,
                        'level'  => $level,
                    ],
                ],
            ],
            'http' => [
                'max_retries' => 1,
                'retry_delay' => 500,
                'timeout'     => 5.0,
            ],
            'oauth' => [
                'scopes'   => ['snsapi_userinfo'],
                'callback' => (string) ($config['oauth_callback'] ?? '/api/wechat/callback'),
            ],
        ];
    }

    /**
     * 获取 access_token
     */
    public static function getAccessToken(): string
    {
        $app = self::make();
        $token = $app->access_token->getToken();
        return (string) ($token['access_token'] ?? '');
    }
}
