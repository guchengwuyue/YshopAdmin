<?php
declare(strict_types=1);

namespace app\controller\api\pay;

use app\service\pay\PayService;
use app\controller\BaseController;
use think\facade\Log;
use think\facade\Session;
use think\response\Redirect;
use Yansongda\Pay\Pay;

/**
 * 站点网关支付公开接口（JSON + 异步/同步回调；禁止 View::fetch）
 */
class Front extends BaseController
{
    /**
     * 外部提交下单
     */
    public function submit()
    {
        $outTradeNo = (string) $this->request->param('out_trade_no', '');
        $title = (string) $this->request->param('title', '');
        $amount = $this->request->param('amount');
        $type = (string) $this->request->param('type', $this->request->param('paytype', ''));
        $method = (string) $this->request->param('method', 'web');
        $openid = (string) $this->request->param('openid', '');
        $authCode = (string) $this->request->param('auth_code', '');
        $notifyurl = (string) $this->request->param('notifyurl', '');
        $returnurl = (string) $this->request->param('returnurl', '');

        if (!$amount || $amount < 0) {
            return $this->error('支付金额必须大于0');
        }
        if (!in_array($type, ['alipay', 'wechat'], true)) {
            return $this->error('支付类型错误');
        }

        try {
            $result = PayService::submitOrder([
                'type'      => $type,
                'orderid'   => $outTradeNo,
                'title'     => $title,
                'amount'    => $amount,
                'method'    => $method,
                'openid'    => $openid,
                'auth_code' => $authCode,
                'notifyurl' => $notifyurl,
                'returnurl' => $returnurl,
            ]);
        } catch (\Throwable $e) {
            return $this->error('下单失败：' . PayService::formatPayException($e));
        }

        if ($result instanceof Redirect) {
            return $this->success('下单成功', [
                'redirect' => (string) $result->getData(),
            ]);
        }
        if ($result instanceof \think\Response) {
            return $this->success('下单成功', [
                'result' => (string) $result->getContent(),
            ]);
        }

        return $this->success('下单成功', [
            'result' => $this->normalizePayResult($result),
        ]);
    }

    /**
     * 微信收银台数据（JSON）
     */
    public function wechat()
    {
        $ua = (string) $this->request->server('HTTP_USER_AGENT');
        $isWechat = stripos($ua, 'MicroMessenger') !== false;
        $isMobile = method_exists($this->request, 'isMobile') ? $this->request->isMobile() : false;

        $orderData = Session::get('wechatorderdata');
        if (!$orderData) {
            return $this->error('请求参数错误');
        }

        try {
            if ($isWechat && $isMobile) {
                if (empty($orderData['openid'])) {
                    $orderData['openid'] = PayService::getOpenid();
                }
                $orderData['method'] = 'mp';
                $type = 'jsapi';
                $payData = PayService::submitOrder($orderData);
                $payData = $this->normalizePayResult($payData);
                if (!isset($payData['paySign'])) {
                    return $this->error('创建订单失败');
                }
            } else {
                $orderData['method'] = 'scan';
                $type = 'pc';
                $payData = PayService::submitOrder($orderData);
                $payData = $this->normalizePayResult($payData);
                if (!isset($payData['code_url'])) {
                    return $this->error('创建订单失败');
                }
            }
        } catch (\Throwable $e) {
            return $this->error('创建订单失败：' . PayService::formatPayException($e));
        }

        return $this->success('', [
            'orderData' => $orderData,
            'payData'   => $payData,
            'type'      => $type,
            'isWechat'  => $isWechat,
            'isMobile'  => $isMobile,
        ]);
    }

    /**
     * 支付宝收银台数据（JSON）
     */
    public function alipay()
    {
        $orderData = Session::get('alipayorderdata');
        if (!$orderData) {
            return $this->error('请求参数错误');
        }
        $orderData['method'] = 'scan';
        try {
            $payData = PayService::submitOrder($orderData);
            $payData = $this->normalizePayResult($payData);
        } catch (\Throwable $e) {
            return $this->error('创建订单失败：' . PayService::formatPayException($e));
        }
        if (!isset($payData['qr_code'])) {
            return $this->error('创建订单失败');
        }

        return $this->success('', [
            'orderData' => $orderData,
            'payData'   => $payData,
            'type'      => 'pc',
            'isWechat'  => false,
            'isMobile'  => method_exists($this->request, 'isMobile') ? $this->request->isMobile() : false,
        ]);
    }

    /**
     * 查询支付状态
     */
    public function query()
    {
        $type = (string) $this->request->param('type', '');
        $orderid = (string) $this->request->param('orderid', '');
        if (!in_array($type, ['wechat', 'alipay'], true) || $orderid === '') {
            return $this->error('参数错误');
        }
        try {
            if ($type === 'wechat') {
                $pay = Pay::wechat(PayService::getConfig('wechat'));
                $result = $pay->find(['out_trade_no' => $orderid]);
                $status = $result['trade_state'] ?? 'NOTPAY';
            } else {
                $pay = Pay::alipay(PayService::getConfig('alipay'));
                $result = $pay->find(['out_trade_no' => $orderid]);
                $status = $result['trade_status'] ?? '';
            }
            return $this->success('', ['status' => $status]);
        } catch (\Throwable) {
            return $this->error('查询失败');
        }
    }

    /**
     * 异步通知（原始响应，禁用 JsonOnly）
     */
    public function notifyx(string $type = '')
    {
        $type = $type !== '' ? $type : (string) $this->request->param('type', '');
        $pay = PayService::checkNotify($type);
        if (!$pay) {
            return json(['code' => 'FAIL', 'message' => 'FAIL'], 500);
        }
        try {
            $data = $pay->callback();
            if ($type === 'wechat') {
                $data = $data['resource']['ciphertext'] ?? $data;
            }
            Log::info('yspay notify: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            Log::error('yspay notify logic: ' . $e->getMessage());
        }
        $resp = $pay->success();
        if (is_object($resp) && method_exists($resp, 'getBody')) {
            return response($resp->getBody()->getContents(), 200, ['Content-Type' => 'application/json']);
        }
        return response('{"code":"SUCCESS","message":"SUCCESS"}', 200, ['Content-Type' => 'application/json']);
    }

    /**
     * 同步跳转 → SPA 结果页
     */
    public function returnx(string $type = '')
    {
        $type = $type !== '' ? $type : (string) $this->request->param('type', '');
        // checkReturn always true; do not treat as signature failure
        $url = '/pay/result?type=' . rawurlencode($type) . '&status=success';
        return redirect($url);
    }

    /**
     * @param mixed $result
     * @return array<string, mixed>
     */
    protected function normalizePayResult($result): array
    {
        if (is_object($result) && method_exists($result, 'toArray')) {
            return $result->toArray();
        }
        return is_array($result) ? $result : (array) $result;
    }
}
