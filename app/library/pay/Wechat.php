<?php
declare(strict_types=1);

namespace app\library\pay;

use think\facade\Session;

/**
 * 微信网页授权
 */
class Wechat
{
    public function __construct(
        protected string $appId,
        protected string $appSecret
    ) {
    }

    /**
     * 获取 openid
     */
    public function getOpenid(): string
    {
        $cached = (string) Session::get('openid', '');
        if ($cached !== '') {
            return $cached;
        }
        $code = (string) request()->param('code', '');
        if ($code === '') {
            $redirect = urlencode(request()->url(true));
            $oauth = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appId}&redirect_uri={$redirect}&response_type=code&scope=snsapi_base&state=pay#wechat_redirect";
            throw new \think\exception\HttpResponseException(redirect($oauth));
        }
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appId}&secret={$this->appSecret}&code={$code}&grant_type=authorization_code";
        $json = @file_get_contents($url);
        $data = $json ? json_decode($json, true) : [];
        $openid = (string) ($data['openid'] ?? '');
        if ($openid !== '') {
            Session::set('openid', $openid);
        }
        return $openid;
    }
}
