<?php
declare(strict_types=1);

namespace app\service\wechat;

use app\library\wechat\response\HandlerRegistry;
use app\model\wechat\WechatAutoreply;
use app\model\wechat\WechatConfig;
use app\model\wechat\WechatContext;
use app\model\wechat\WechatResponse;

/**
 * 公众号消息分发
 */
class MessageDispatchService
{
    /**
     * 处理入站消息
     * @param array $message EasyWeChat 消息数组
     * @return mixed
     */
    public function handle(array $message)
    {
        $matches = null;
        $openid = (string) ($message['FromUserName'] ?? '');
        $unknownMessage = WechatConfig::getValue('default.unknown.message');

        $msgType = (string) ($message['MsgType'] ?? '');
        if ($msgType === 'event') {
            return $this->handleEvent($message, $openid, $unknownMessage);
        }

        if ($msgType === 'text') {
            $content = (string) ($message['Content'] ?? '');
            $autoreplyList = WechatAutoreply::where('status', 'normal')
                ->field('id,text,eventkey')
                ->order('weigh', 'desc')
                ->order('id', 'desc')
                ->select();
            try {
                foreach ($autoreplyList as $item) {
                    $text = (string) $item['text'];
                    $first = mb_substr($text, 0, 1);
                    if ($text === $content || (in_array($first, ['#', '~', '/'], true) && preg_match($text, $content, $matches))) {
                        $result = $this->resolveResponse((string) $item['eventkey'], $openid, $content, $matches);
                        if ($result !== null && $result !== '') {
                            return $result;
                        }
                        break;
                    }
                }
            } catch (\Throwable $e) {
                // ignore regex errors
            }
        }

        return $unknownMessage;
    }

    /**
     * 事件消息
     */
    protected function handleEvent(array $message, string $openid, string $unknownMessage)
    {
        $event = (string) ($message['Event'] ?? '');
        $eventkey = (string) (($message['EventKey'] ?? '') ?: $event);

        if (in_array($event, ['subscribe', 'SCAN'], true)
            && preg_match('/^(qrscene_)?captcha_([a-zA-Z0-9]+)_([0-9.]+)_([0-9a-zA-Z]{6})/', $eventkey, $matches)) {
            return CaptchaService::replyCode($openid, $matches[2], $matches[3], $matches[4]);
        }

        switch ($event) {
            case 'subscribe':
                $msg = WechatConfig::getValue('default.subscribe.message');
                return $msg !== '' ? $msg : '欢迎关注我们!';
            case 'unsubscribe':
            case 'LOCATION':
            case 'VIEW':
            case 'SCAN':
                return '';
            default:
                break;
        }

        $result = $this->resolveResponse($eventkey, $openid, '', null, true);
        if ($result !== null && $result !== '') {
            return $result;
        }
        return $unknownMessage;
    }

    /**
     * 按 eventkey 解析资源并走 handler
     * @return mixed
     */
    protected function resolveResponse(
        string $eventkey,
        string $openid,
        string $message,
        ?array $matches = null,
        bool $touchContext = false
    ) {
        $row = WechatResponse::where(['eventkey' => $eventkey, 'status' => 'normal'])->find();
        if (!$row) {
            return null;
        }
        $responseContent = (array) json_decode((string) $row['content'], true);
        $context = WechatContext::where('openid', $openid)->order('id', 'desc')->find();
        if ($touchContext) {
            $data = [
                'eventkey'    => $eventkey,
                'command'     => '',
                'refreshtime' => time(),
                'openid'      => $openid,
            ];
            if ($context) {
                $context->save($data);
            } else {
                $data['createtime'] = time();
                $data['updatetime'] = time();
                $context = WechatContext::create($data);
            }
        }
        $contextArr = $context ? $context->toArray() : null;
        return $this->dispatchHandler($openid, $message, $responseContent, $contextArr, $matches);
    }

    /**
     * 调用可插拔 handler
     * @return mixed
     */
    public function dispatchHandler(
        string $openid,
        string $message,
        array $content,
        ?array $context = null,
        ?array $matches = null
    ) {
        if (isset($content['app'])) {
            $app = (string) $content['app'];
            $handler = HandlerRegistry::get($app);
            if (!$handler || !$handler->available()) {
                return '';
            }
            $keyword = '';
            if (isset($content['searchregexindex']) && (int) $content['searchregexindex'] > -1
                && $matches && isset($matches[(int) $content['searchregexindex']])) {
                $keyword = (string) $matches[(int) $content['searchregexindex']];
            } else {
                $keyword = $message;
            }
            return $handler->response($openid, $message, $content, $context, $matches, $keyword);
        }

        $handler = HandlerRegistry::get('text');
        if ($handler) {
            return $handler->response($openid, $message, $content, $context, $matches, $message);
        }
        return (string) ($content['content'] ?? '');
    }
}
