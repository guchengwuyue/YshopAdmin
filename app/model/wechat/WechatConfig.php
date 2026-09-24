<?php
declare(strict_types=1);

namespace app\model\wechat;

use think\Model;

/**
 * 业务配置 KV
 */
class WechatConfig extends Model
{
    protected $name = 'wechat_config';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    /** @var array bigint 时间戳需声明为 integer，否则会写入日期字符串导致截断 */
    protected $type = [
        'createtime' => 'integer',
        'updatetime' => 'integer',
    ];

    /**
     * 读取配置值
     */
    public static function getValue(string $name): string
    {
        $item = self::where('name', $name)->find();
        return $item ? (string) $item->value : '';
    }
}
