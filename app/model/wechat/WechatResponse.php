<?php
declare(strict_types=1);

namespace app\model\wechat;

use think\Model;

/**
 * 回复资源
 */
class WechatResponse extends Model
{
    protected $name = 'wechat_response';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $type = [
        'createtime' => 'integer',
        'updatetime' => 'integer',
    ];
}
