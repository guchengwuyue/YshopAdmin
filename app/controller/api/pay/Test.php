<?php
declare(strict_types=1);

namespace app\controller\api\pay;

use app\service\pay\PayService;
use app\controller\BaseController;
use think\response\Redirect;

/**
 * 支付测试 JSON API
 */
class Test extends BaseController
{
    public function pay()
    {
        $amount = $this->request->param('amount', '0.01');
        $type = (string) $this->request->param('type', 'wechat');
        $method = (string) $this->request->param('method', 'web');
        $openid = (string) $this->request->param('openid', '');
        $authCode = (string) $this->request->param('auth_code', $this->request->param('authCode', ''));

        if (!is_numeric($amount) || (float) $amount <= 0) {
            return $this->error('支付金额必须大于0');
        }
        if (!in_array($type, ['wechat', 'alipay'], true)) {
            return $this->error('支付类型错误');
        }
        $methods = ['web', 'wap', 'h5', 'app', 'scan', 'mp', 'mini', 'miniapp', 'pos'];
        if (!in_array($method, $methods, true)) {
            return $this->error('支付场景错误');
        }
        if ($type === 'alipay' && $method === 'mp') {
            return $this->error('支付宝不支持公众号支付');
        }
        if (in_array($method, ['mp', 'mini', 'miniapp'], true) && $openid === '') {
            return $this->error('该场景需要填写 openid');
        }
        if ($method === 'pos' && $authCode === '') {
            return $this->error('付款码支付需要填写 auth_code');
        }

        $orderid = date('YmdHis') . mt_rand(100000, 999999);
        try {
            $result = PayService::submitOrder([
                'amount'    => $amount,
                'orderid'   => $orderid,
                'type'      => $type,
                'title'     => '支付测试订单',
                'method'    => $method,
                'openid'    => $openid,
                'auth_code' => $authCode,
            ]);
        } catch (\Throwable $e) {
            return $this->error('下单失败：' . PayService::formatPayException($e));
        }

        if ($result instanceof Redirect) {
            return $this->success('下单成功', [
                'orderid'  => $orderid,
                'type'     => $type,
                'method'   => $method,
                'redirect' => (string) $result->getData(),
            ]);
        }
        if ($result instanceof \think\Response) {
            return $this->success('下单成功', [
                'orderid' => $orderid,
                'type'    => $type,
                'method'  => $method,
                'result'  => (string) $result->getContent(),
            ]);
        }
        return $this->success('下单成功', [
            'orderid' => $orderid,
            'type'    => $type,
            'method'  => $method,
            'result'  => $this->normalize($result),
        ]);
    }

    private function normalize(mixed $result): mixed
    {
        if (is_object($result) && method_exists($result, 'toArray')) {
            return $result->toArray();
        }
        if (is_object($result) && method_exists($result, '__toString')) {
            return (string) $result;
        }
        return $result;
    }
}
