<?php
declare(strict_types=1);

namespace app\model\wechat;

use think\Model;

/**
 * 自动回复
 */
class WechatAutoreply extends Model
{
    protected $name = 'wechat_autoreply';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $type = [
        'createtime' => 'integer',
        'updatetime' => 'integer',
        'weigh'      => 'integer',
    ];
}
