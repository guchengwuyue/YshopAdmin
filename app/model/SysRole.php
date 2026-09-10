<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class SysRole extends Model
{
    protected $name = 'sys_role';
    protected $pk = 'role_id';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
}
