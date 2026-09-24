<?php
declare(strict_types=1);

namespace app\service\pay;

use app\common\BuiltinConfig;

/**
 * 支付配置（系统内置）
 */
class ConfigService
{
    /**
     * 读取微信/支付宝配置
     * @return array{version:string,wechat:array,alipay:array}
     */
    public function load(): array
    {
        $config = BuiltinConfig::get('yspay');
        $defaults = $this->schemaDefaults();
        $wechat = $this->normalizeChannel(array_merge($defaults['wechat'], (array) ($config['wechat'] ?? [])));
        $alipay = $this->normalizeChannel(array_merge($defaults['alipay'], (array) ($config['alipay'] ?? [])));
        unset($wechat['key']);
        return [
            'version' => 'v3',
            'wechat'  => $wechat,
            'alipay'  => $alipay,
        ];
    }

    /**
     * 保存配置
     */
    public function save(array $post): void
    {
        $defaults = $this->schemaDefaults();
        $wechat = $this->normalizeChannel(array_merge($defaults['wechat'], (array) ($post['wechat'] ?? [])));
        $alipay = $this->normalizeChannel(array_merge($defaults['alipay'], (array) ($post['alipay'] ?? [])));
        unset($wechat['key']);
        BuiltinConfig::set('yspay', [
            'version' => 'v3',
            'wechat'  => $wechat,
            'alipay'  => $alipay,
        ]);
    }

    /**
     * @return array{wechat: array, alipay: array}
     */
    private function schemaDefaults(): array
    {
        $schema = BuiltinConfig::schema('yspay');
        $wechat = [];
        $alipay = [];
        foreach ($schema as $item) {
            if (!is_array($item) || empty($item['name'])) {
                continue;
            }
            if ($item['name'] === 'wechat' && is_array($item['value'] ?? null)) {
                $wechat = $item['value'];
            }
            if ($item['name'] === 'alipay' && is_array($item['value'] ?? null)) {
                $alipay = $item['value'];
            }
        }
        return ['wechat' => $wechat, 'alipay' => $alipay];
    }

    private function normalizeChannel(array $cfg): array
    {
        foreach (['log', 'scanpay', 'mode', 'signtype'] as $key) {
            if (array_key_exists($key, $cfg) && !is_array($cfg[$key])) {
                $cfg[$key] = (string) $cfg[$key];
            }
        }
        return $cfg;
    }
}
