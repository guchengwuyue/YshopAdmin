<?php
declare(strict_types=1);

namespace app\controller\api\wechat;

use app\service\wechat\CaptchaService;
use app\service\wechat\MessageDispatchService;
use app\service\wechat\OfficialAccountFactory;
use app\controller\BaseController;

/**
 * 站点网关微信公开接口（server/callback 原始响应；captcha 走 JSON）
 */
class Front extends BaseController
{
    /**
     * 服务器 URL 回调（原始 XML/响应，禁用 JsonOnly）
     */
    public function server()
    {
        $app = OfficialAccountFactory::make();
        $dispatch = new MessageDispatchService();
        $app->server->push(function ($message) use ($dispatch) {
            return $dispatch->handle(is_array($message) ? $message : (array) $message);
        });
        $response = $app->server->serve();
        $response->send();
        exit;
    }

    /**
     * OAuth 回调占位（待扩展）
     */
    public function callback()
    {
        return $this->success('OAuth callback 待扩展');
    }

    /**
     * 发送扫码验证码
     */
    public function captchaSend()
    {
        try {
            $event = (string) $this->request->post('event', '');
            $data = (new CaptchaService())->send($event, $this->request->ip());
            return $this->success('ok', $data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 校验扫码验证码
     */
    public function captchaCheck()
    {
        $captcha = (string) $this->request->post('captcha', '');
        $event = (string) $this->request->post('event', '');
        if ((new CaptchaService())->check($captcha, $event, null, false)) {
            return $this->success('验证码正确');
        }
        return $this->error('验证码错误');
    }
}
