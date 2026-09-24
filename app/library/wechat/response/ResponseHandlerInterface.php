<?php
declare(strict_types=1);

namespace app\library\wechat\response;

/**
 * 可插拔回复处理器
 */
interface ResponseHandlerInterface
{
    /**
     * 是否可用
     */
    public function available(): bool;

    /**
     * 后台配置元数据
     * @return array{name:string,fields?:array}
     */
    public function config(): array;

    /**
     * 处理回复
     * @return mixed
     */
    public function response(
        string $openid,
        string $message,
        array $content,
        ?array $context = null,
        ?array $matches = null,
        string $keyword = ''
    );
}
