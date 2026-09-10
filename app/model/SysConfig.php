<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class SysConfig extends Model
{
    protected $name = 'sys_config';
    protected $pk = 'config_id';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
}
