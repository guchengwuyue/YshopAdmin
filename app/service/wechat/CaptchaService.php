<?php
declare(strict_types=1);

namespace app\service\wechat;

use app\model\wechat\WechatCaptcha;
use app\service\wechat\OfficialAccountFactory;
use think\facade\Cookie;
use think\facade\Request;

/**
 * 公众号扫码验证码
 */
class CaptchaService
{
    /**
     * 扫码后回复验证码文案
     */
    public static function replyCode(string $openid, string $event, string $ip, string $context = ''): string
    {
        $exists = WechatCaptcha::where(['openid' => $openid, 'event' => $event])
            ->where('createtime', '>', time() - 120)
            ->find();
        if ($exists) {
            return '验证码发送速度过快，请稍后重试';
        }
        $code = (string) random_int(100000, 999999);
        WechatCaptcha::create([
            'event'      => $event,
            'openid'     => $openid,
            'code'       => $code,
            'ip'         => $ip,
            'context'    => $context,
            'times'      => 0,
            'createtime' => time(),
        ]);
        return "你的验证码是：{$code}，2分钟内输入有效";
    }

    /**
     * 生成带参二维码供前端展示
     * @return array{image:string,url:string}
     */
    public function send(string $event, string $ip): array
    {
        if ($event === '') {
            throw new \InvalidArgumentException('参数错误');
        }
        $recent = WechatCaptcha::where('ip', $ip)
            ->where('event', $event)
            ->where('createtime', '>', time() - 120)
            ->find();
        if ($recent) {
            throw new \RuntimeException('获取频繁，请稍后重试');
        }
        $token = OfficialAccountFactory::getAccessToken();
        if ($token === '') {
            throw new \RuntimeException('发送失败，请稍后重试');
        }
        $context = substr(bin2hex(random_bytes(4)), 0, 6);
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $token;
        $params = [
            'expire_seconds' => 120,
            'action_name'    => 'QR_STR_SCENE',
            'action_info'    => [
                'scene' => [
                    'scene_str' => 'captcha_' . $event . '_' . $ip . '_' . $context,
                ],
            ],
        ];
        $client = new \GuzzleHttp\Client(['timeout' => 8]);
        $resp = $client->post($url, ['json' => $params]);
        $msg = json_decode((string) $resp->getBody(), true) ?: [];
        if (!isset($msg['ticket'], $msg['url'])) {
            throw new \RuntimeException('获取失败！请稍后重试');
        }
        Cookie::set('captcha_' . $event, $context);
        return [
            'image' => 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode((string) $msg['ticket']),
            'url'   => (string) $msg['url'],
        ];
    }

    /**
     * 校验验证码
     */
    public function check(string $code, string $event, ?string $ip = null, bool $afterDelete = true): bool
    {
        $ip = $ip ?? Request::ip();
        $context = (string) Cookie::get('captcha_' . $event);
        $captcha = WechatCaptcha::where([
            'ip'      => $ip,
            'event'   => $event,
            'context' => $context,
        ])->where('createtime', '>', time() - 120)->find();

        if ($captcha && (string) $captcha->code === $code && (int) $captcha->times < 10 && $context !== '') {
            $captcha->times = (int) $captcha->times + 1;
            $captcha->save();
            if ($afterDelete) {
                $captcha->delete();
            }
            return true;
        }
        if ($captcha && ((int) $captcha->times >= 10 || time() - (int) $captcha->createtime > 120)) {
            $captcha->delete();
        }
        return false;
    }
}
