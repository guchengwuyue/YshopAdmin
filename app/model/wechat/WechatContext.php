<?php
declare(strict_types=1);

namespace app\model\wechat;

use think\Model;

/**
 * 会话上下文
 */
class WechatContext extends Model
{
    protected $name = 'wechat_context';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $type = [
        'createtime'  => 'integer',
        'updatetime'  => 'integer',
        'refreshtime' => 'integer',
    ];
}
