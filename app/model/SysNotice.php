<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class SysNotice extends Model
{
    protected $name = 'sys_notice';
    protected $pk = 'notice_id';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
}
