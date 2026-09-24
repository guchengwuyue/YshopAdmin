<?php
declare(strict_types=1);

namespace app\service\pay;

use app\library\pay\Wechat;
use think\facade\Log;
use think\facade\Session;
use Yansongda\Pay\Pay;

/**
 * 支付下单与回调
 */
class PayService
{
    public const SDK_VERSION_V2 = 'v2';
    public const SDK_VERSION_V3 = 'v3';

    /**
     * 提交支付订单
     * @param array|float|string $amount
     */
    public static function submitOrder(
        $amount,
        $orderid = null,
        $type = null,
        $title = null,
        $notifyurl = null,
        $returnurl = null,
        $method = null,
        $openid = '',
        $custom = []
    ): mixed {
        if (!self::isVersionV3()) {
            throw new \RuntimeException('当前仅支持 Composer yansongda/pay V3');
        }

        $request = request();
        $addonConfig = \app\common\BuiltinConfig::get('yspay');

        if (!is_array($amount)) {
            $params = [
                'amount'    => $amount,
                'orderid'   => $orderid,
                'type'      => $type,
                'title'     => $title,
                'notifyurl' => $notifyurl,
                'returnurl' => $returnurl,
                'method'    => $method,
                'openid'    => $openid,
                'custom'    => $custom,
            ];
        } else {
            $params = $amount;
        }

        if (empty($params['orderid']) && !empty($params['out_trade_no'])) {
            $params['orderid'] = $params['out_trade_no'];
        }

        $type = isset($params['type']) && in_array($params['type'], ['alipay', 'wechat'], true) ? $params['type'] : 'wechat';
        $method = $params['method'] ?? 'web';
        $orderid = $params['orderid'] ?? (date('YmdHis') . mt_rand(100000, 999999));
        $amount = $params['amount'] ?? 1;
        $title = $params['title'] ?? '支付';
        $auth_code = $params['auth_code'] ?? '';
        $openid = $params['openid'] ?? '';
        $custom = $params['custom'] ?? [];

        // Callbacks/cashier pages are front public routes — never under /yshopadmin
        $domain = rtrim((string) $request->domain(), '/');
        $notifyurl = !empty($params['notifyurl'])
            ? $params['notifyurl']
            : $domain . '/api/pay/notifyx/' . $type;
        $returnurl = !empty($params['returnurl'])
            ? $params['returnurl']
            : $domain . '/api/pay/returnx/' . $type;

        $config = self::getConfig($type, array_merge($custom, [
            'notify_url' => $notifyurl,
            'return_url' => $returnurl,
        ]));
        self::assertChannelReady($type, $config, $method);

        $isMobile = method_exists($request, 'isMobile') ? $request->isMobile() : false;
        $ua = (string) $request->server('HTTP_USER_AGENT');
        $isWechat = stripos($ua, 'MicroMessenger') !== false;

        $result = null;
        if ($type === 'alipay') {
            if ($method === 'web') {
                if ($isWechat || !empty($addonConfig['alipay']['scanpay'])) {
                    Session::set('alipayorderdata', $params);
                    return redirect($domain . '/pay/alipay');
                }
                if ($isMobile) {
                    $method = 'h5';
                }
            }
            if ($method === 'wap') {
                $method = 'h5';
            }
            $pay = Pay::alipay($config);
            $order = [
                'out_trade_no' => $orderid,
                'total_amount' => $amount,
                'subject'      => $title,
            ];
            $result = match ($method) {
                'web' => $pay->web($order),
                'h5' => $pay->h5($order),
                'app' => $pay->app($order),
                'scan' => $pay->scan($order),
                'pos' => $pay->pos(array_merge($order, ['auth_code' => $auth_code])),
                'mini', 'miniapp' => $pay->mini(array_merge($order, is_numeric($openid) && strlen((string) $openid) === 16
                    ? ['buyer_id' => $openid]
                    : ['buyer_open_id' => $openid])),
                default => null,
            };
        } else {
            if ($method === 'web') {
                if ($isMobile && !$isWechat) {
                    $method = 'h5';
                } else {
                    Session::set('wechatorderdata', $params);
                    return redirect($domain . '/pay/wechat');
                }
            }
            if ($method === 'wap') {
                $method = 'h5';
            }
            $totalFee = (int) (function_exists('bcmul') ? bcmul((string) $amount, '100') : $amount * 100);
            $ip = $request->ip();
            $openidName = (($addonConfig['wechat']['mode'] ?? '') === 'service') ? 'sub_openid' : 'openid';
            $pay = Pay::wechat($config);
            $order = [
                'out_trade_no' => $orderid,
                'description'  => $title,
                'amount'       => ['total' => $totalFee],
            ];
            $result = match ($method) {
                'mp' => $pay->mp(array_merge($order, ['payer' => [$openidName => $openid]])),
                'h5' => $pay->h5(array_merge($order, [
                    'scene_info' => [
                        'payer_client_ip' => $ip,
                        'h5_info'         => ['type' => 'Wap'],
                    ],
                ])),
                'app' => $pay->app($order),
                'scan' => $pay->scan($order),
                'pos' => $pay->pos(array_merge($order, ['auth_code' => $auth_code])),
                'mini', 'miniapp' => $pay->mini(array_merge($order, ['payer' => [$openidName => $openid]])),
                default => null,
            };
            // WeChat H5 returns h5_url — jump to WeChat cashier
            if ($method === 'h5' && $result !== null) {
                $h5Data = is_object($result) && method_exists($result, 'toArray')
                    ? $result->toArray()
                    : (array) $result;
                if (!empty($h5Data['h5_url'])) {
                    return redirect((string) $h5Data['h5_url']);
                }
            }
        }

        return $result;
    }

    /**
     * 验证异步通知
     */
    public static function checkNotify(string $type, array $custom = []): mixed
    {
        $type = strtolower($type);
        if (!in_array($type, ['wechat', 'alipay'], true)) {
            return false;
        }
        if (!self::isVersionV3()) {
            return false;
        }
        try {
            $config = self::getConfig($type, $custom);
            $pay = $type === 'wechat' ? Pay::wechat($config) : Pay::alipay($config);
            $data = $pay->callback();
            if ($type === 'alipay') {
                $status = $data['trade_status'] ?? '';
                if (in_array($status, ['TRADE_SUCCESS', 'TRADE_FINISHED'], true)) {
                    return $pay;
                }
                return false;
            }
            return $pay;
        } catch (\Throwable $e) {
            Log::error('yspay notify parse error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 验证同步跳转
     * @deprecated
     */
    public static function checkReturn(string $type, array $custom = []): bool
    {
        return true;
    }

    /**
     * 获取支付配置
     */
    public static function getConfig(string $type = 'wechat', array $custom = []): array
    {
        self::registerPayPatches();
        $addonConfig = \app\common\BuiltinConfig::get('yspay');
        $config = $addonConfig[$type] ?? ($addonConfig['wechat'] ?? []);

        if ($type === 'wechat') {
            foreach (['cert_client', 'cert_key', 'public_key'] as $field) {
                self::processAddonsPath($config, $field);
            }
            // 配置表单: app_id=公众号, appid=APP, miniapp_id=小程序；SDK 默认 Native/web 用 mp_app_id
            $mpAppId = trim((string) ($config['app_id'] ?? ''));
            $appAppId = trim((string) ($config['appid'] ?? ''));
            $miniAppId = trim((string) ($config['miniapp_id'] ?? ''));
            $fallbackAppId = $mpAppId !== '' ? $mpAppId : ($appAppId !== '' ? $appAppId : $miniAppId);
            $config['mp_app_id'] = $mpAppId !== '' ? $mpAppId : $fallbackAppId;
            $config['app_id'] = $appAppId !== '' ? $appAppId : $fallbackAppId;
            $config['mini_app_id'] = $miniAppId !== '' ? $miniAppId : $fallbackAppId;
            $config['mch_secret_key'] = $config['key_v3'] ?? '';
            $config['mch_secret_cert'] = $config['cert_key'] ?? '';
            $config['mch_public_cert_path'] = $config['cert_client'] ?? '';
            $publicKeyId = trim((string) ($config['public_key_id'] ?? ''));
            $publicKeyPath = (string) ($config['public_key'] ?? '');
            $config['wechat_public_cert_path'] = ($publicKeyId !== '' && $publicKeyPath !== '')
                ? [$publicKeyId => $publicKeyPath]
                : [];
            $config['sub_mp_app_id'] = $config['sub_appid'] ?? '';
            $config['sub_mini_app_id'] = $config['sub_miniapp_id'] ?? '';
        } elseif ($type === 'alipay') {
            $config['signtype'] = $config['signtype'] ?? 'publickey';
            $config['app_secret_cert'] = $config['private_key'] ?? '';
            $config['service_provider_id'] = $config['pid'] ?? '';
            if (($config['signtype'] ?? '') === 'cert') {
                foreach (['app_cert_public_key', 'alipay_root_cert', 'ali_public_key'] as $field) {
                    self::processAddonsPath($config, $field);
                }
                $config['app_public_cert_path'] = $config['app_cert_public_key'] ?? '';
                $config['alipay_public_cert_path'] = $config['ali_public_key'] ?? '';
                $config['alipay_root_cert_path'] = $config['alipay_root_cert'] ?? '';
            } else {
                // Ordinary public key: no app/root cert; PEM-wrap alipay public key for verify
                $config['app_cert_public_key'] = '';
                $config['alipay_root_cert'] = '';
                $config['app_public_cert_path'] = '';
                $config['alipay_root_cert_path'] = '';
                $config['alipay_public_cert_path'] = self::normalizeAlipayPublicKey((string) ($config['ali_public_key'] ?? ''));
            }
        }

        $modeArr = ['normal' => 0, 'dev' => 1, 'service' => 2];
        $config['mode'] = $modeArr[$config['mode'] ?? 'normal'] ?? 0;

        if (!empty($config['log'])) {
            $logDir = runtime_path() . 'yspay' . DIRECTORY_SEPARATOR;
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $config['log'] = [
                'enable' => true,
                'file'   => $logDir . $type . '-' . date('Y-m-d') . '.log',
                'level'  => 'debug',
            ];
        } else {
            $config['log'] = ['enable' => false];
        }

        $http = ['timeout' => 10, 'connect_timeout' => 10];
        $caBundle = root_path() . 'certs' . DIRECTORY_SEPARATOR . 'cacert.pem';
        if (is_file($caBundle)) {
            $http['verify'] = $caBundle;
        }
        $config['http'] = $http;

        $domain = rtrim((string) request()->domain(), '/');
        $config['notify_url'] = empty($config['notify_url'])
            ? $domain . '/api/pay/notifyx/' . $type
            : $config['notify_url'];
        if (!preg_match('#^https?://#i', (string) $config['notify_url'])) {
            $config['notify_url'] = \app\common\Entry::publicRoot(true) . $config['notify_url'];
        }
        $config['return_url'] = empty($config['return_url'])
            ? $domain . '/api/pay/returnx/' . $type
            : $config['return_url'];
        if (!preg_match('#^https?://#i', (string) $config['return_url'])) {
            $config['return_url'] = \app\common\Entry::publicRoot(true) . $config['return_url'];
        }

        $config = array_merge($config, $custom);

        return [
            $type    => ['default' => $config],
            'logger' => $config['log'],
            'http'   => $config['http'],
            '_force' => true,
        ];
    }

    /**
     * 获取微信 Openid
     */
    public static function getOpenid(array $custom = []): string
    {
        $openid = (string) Session::get('openid', '');
        if ($openid !== '') {
            return $openid;
        }
        $addonConfig = \app\common\BuiltinConfig::get('yspay');
        $appId = $custom['app_id'] ?? ($addonConfig['wechat']['app_id'] ?? '');
        $secret = $custom['app_secret'] ?? ($addonConfig['wechat']['app_secret'] ?? '');
        if ($appId === '' || $secret === '') {
            return '';
        }
        $wechat = new Wechat($appId, $secret);
        return (string) $wechat->getOpenid();
    }

    /**
     * 获取 SDK 版本
     */
    public static function getSdkVersion(): string
    {
        $addonConfig = \app\common\BuiltinConfig::get('yspay');
        return $addonConfig['version'] ?? self::SDK_VERSION_V3;
    }

    public static function isVersionV2(): bool
    {
        return self::getSdkVersion() === self::SDK_VERSION_V2;
    }

    public static function isVersionV3(): bool
    {
        return self::getSdkVersion() === self::SDK_VERSION_V3;
    }

    private static function processAddonsPath(array &$config, string $field): void
    {
        if (!isset($config[$field]) || !is_string($config[$field])) {
            return;
        }
        $val = $config[$field];
        if (str_starts_with($val, '/data/certs/') || str_starts_with($val, '/addons/yspay/certs/')) {
            $config[$field] = root_path() . str_replace('/', DIRECTORY_SEPARATOR, ltrim($val, '/'));
            // legacy addon cert path → data/certs/pay
            if (str_starts_with($val, '/addons/yspay/certs/')) {
                $name = basename($val);
                $config[$field] = \app\common\BuiltinConfig::certsDir() . $name;
            }
        } elseif (str_starts_with($val, '/addons/')) {
            $config[$field] = root_path() . str_replace('/', DIRECTORY_SEPARATOR, ltrim($val, '/'));
        }
    }

    /**
     * 下单前校验渠道必填项，避免只看到 SDK 笼统的状态码异常
     */
    private static function assertChannelReady(string $type, array $payConfig, string $method): void
    {
        $channel = $payConfig[$type]['default'] ?? [];
        if ($type === 'wechat') {
            if (trim((string) ($channel['mch_id'] ?? '')) === '') {
                throw new \RuntimeException('请先在支付配置中填写微信商户号 mch_id');
            }
            if (trim((string) ($channel['mch_secret_key'] ?? '')) === '') {
                throw new \RuntimeException('请先在支付配置中填写微信 APIv3 密钥 key_v3');
            }
            $appIds = [
                trim((string) ($channel['mp_app_id'] ?? '')),
                trim((string) ($channel['app_id'] ?? '')),
                trim((string) ($channel['mini_app_id'] ?? '')),
            ];
            if ($appIds[0] === '' && $appIds[1] === '' && $appIds[2] === '') {
                throw new \RuntimeException('请先填写至少一个微信 AppID（公众号 app_id / APP appid / 小程序 miniapp_id）。扫码与 web 默认使用公众号 AppID');
            }
            foreach (['mch_secret_cert' => '商户私钥 cert_key', 'mch_public_cert_path' => '商户证书 cert_client'] as $key => $label) {
                $path = (string) ($channel[$key] ?? '');
                if ($path === '' || !is_file($path)) {
                    throw new \RuntimeException('微信' . $label . '文件不存在，请重新上传到 data/certs/pay/');
                }
            }
            $pubMap = $channel['wechat_public_cert_path'] ?? [];
            if (!is_array($pubMap) || $pubMap === []) {
                throw new \RuntimeException('请填写微信支付公钥 ID，并上传 public_key.pem');
            }
            foreach ($pubMap as $pubPath) {
                if (!is_string($pubPath) || $pubPath === '' || !is_file($pubPath)) {
                    throw new \RuntimeException('微信支付公钥文件不存在，请上传 public_key.pem 到 data/certs/pay/');
                }
            }
            if (in_array($method, ['mp', 'mini', 'miniapp'], true)) {
                // openid 已在测试页校验；此处仅提示场景对应 AppID
                $need = $method === 'mp' ? ($channel['mp_app_id'] ?? '') : ($channel['mini_app_id'] ?? '');
                if (trim((string) $need) === '') {
                    throw new \RuntimeException($method === 'mp' ? '公众号支付请填写公众号 AppID（app_id）' : '小程序支付请填写小程序 AppID（miniapp_id）');
                }
            }
            return;
        }

        if (trim((string) ($channel['app_id'] ?? '')) === '') {
            throw new \RuntimeException('请先在支付配置中填写支付宝 app_id');
        }
        if (trim((string) ($channel['app_secret_cert'] ?? '')) === '') {
            throw new \RuntimeException('请先在支付配置中填写支付宝应用私钥');
        }
        $signType = (string) ($channel['signtype'] ?? 'publickey');
        if ($signType === 'cert') {
            foreach ([
                'app_public_cert_path'     => '应用公钥证书',
                'alipay_public_cert_path'  => '支付宝公钥证书',
                'alipay_root_cert_path'    => '支付宝根证书',
            ] as $key => $label) {
                $path = (string) ($channel[$key] ?? '');
                if ($path === '' || !is_file($path)) {
                    throw new \RuntimeException('支付宝' . $label . '文件不存在，请上传到 data/certs/pay/');
                }
            }
        } elseif (trim((string) ($channel['alipay_public_cert_path'] ?? '')) === '') {
            throw new \RuntimeException('请先在支付配置中填写支付宝公钥（普通公钥模式）');
        }
    }

    /**
     * Override yansongda StartPlugin so publickey mode skips empty cert SN parsing.
     */
    public static function registerPayPatches(): void
    {
        static $registered = false;
        if ($registered) {
            return;
        }
        $registered = true;
        $class = 'Yansongda\\Pay\\Plugin\\Alipay\\V2\\StartPlugin';
        if (class_exists($class, false)) {
            return;
        }
        $file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'pay' . DIRECTORY_SEPARATOR . 'StartPlugin.php';
        if (!is_file($file)) {
            return;
        }
        spl_autoload_register(static function (string $name) use ($class, $file): void {
            if ($name === $class) {
                require $file;
            }
        }, true, true);
    }

    /**
     * Wrap raw Alipay public key base64 as PEM for openssl_verify.
     */
    private static function normalizeAlipayPublicKey(string $key): string
    {
        $key = trim($key);
        if ($key === '') {
            return '';
        }
        if (str_starts_with($key, '/addons/') || str_starts_with($key, '/data/certs/')) {
            $tmp = ['k' => $key];
            self::processAddonsPath($tmp, 'k');
            return $tmp['k'];
        }
        $lower = strtolower($key);
        if (
            str_ends_with($lower, '.cer')
            || str_ends_with($lower, '.crt')
            || str_ends_with($lower, '.pem')
            || stripos($key, 'BEGIN PUBLIC KEY') !== false
            || stripos($key, 'BEGIN CERTIFICATE') !== false
        ) {
            return $key;
        }
        return "-----BEGIN PUBLIC KEY-----\n"
            . wordwrap($key, 64, "\n", true)
            . "\n-----END PUBLIC KEY-----";
    }

    /**
     * 从支付 SDK 异常中提取微信/支付宝返回正文
     */
    public static function formatPayException(\Throwable $e): string
    {
        $msg = $e->getMessage();
        $extra = null;
        if (property_exists($e, 'response')) {
            /** @var mixed $extra */
            $extra = $e->response ?? null;
        }
        if ($extra === null && method_exists($e, 'getExtra')) {
            $extra = $e->getExtra();
        }
        if ($extra === null) {
            return $msg;
        }
        if (is_object($extra) && method_exists($extra, 'toArray')) {
            $extra = $extra->toArray();
        }
        if ($extra instanceof \JsonSerializable) {
            $extra = $extra->jsonSerialize();
        }
        $flags = JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE;
        if (is_array($extra) || is_object($extra)) {
            $json = json_encode($extra, $flags);
            if (is_string($json) && $json !== '[]' && $json !== '{}') {
                return $msg . ' | ' . $json;
            }
        } elseif (is_string($extra) && $extra !== '') {
            if (!mb_check_encoding($extra, 'UTF-8')) {
                $extra = mb_convert_encoding($extra, 'UTF-8', 'UTF-8,GBK,GB18030,CP936');
            }
            return $msg . ' | ' . $extra;
        }
        return $msg;
    }
}

// Load early so StartPlugin resolves to the publickey-capable override.
PayService::registerPayPatches();
