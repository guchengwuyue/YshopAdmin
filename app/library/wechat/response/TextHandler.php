<?php
declare(strict_types=1);

namespace app\library\wechat\response;

/**
 * 文本回复
 */
class TextHandler implements ResponseHandlerInterface
{
    public function available(): bool
    {
        return true;
    }

    public function config(): array
    {
        return [
            'name'   => '文本',
            'fields' => [
                ['name' => 'content', 'title' => '文本内容', 'type' => 'textarea'],
            ],
        ];
    }

    public function response(
        string $openid,
        string $message,
        array $content,
        ?array $context = null,
        ?array $matches = null,
        string $keyword = ''
    ) {
        return (string) ($content['content'] ?? '');
    }
}
