<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class SysUser extends Model
{
    protected $name = 'sys_user';
    protected $pk = 'user_id';
    protected $hidden = ['password', 'salt'];
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
}
