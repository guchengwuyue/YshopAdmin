<?php
declare(strict_types=1);

namespace app\model\wechat;

use think\Model;

/**
 * 扫码验证码
 */
class WechatCaptcha extends Model
{
    protected $name = 'wechat_captcha';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $type = [
        'createtime' => 'integer',
        'times'      => 'integer',
    ];
}
