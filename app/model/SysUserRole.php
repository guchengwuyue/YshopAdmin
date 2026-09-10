<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class SysUserRole extends Model
{
    protected $name = 'sys_user_role';
    protected $autoWriteTimestamp = false;
}
