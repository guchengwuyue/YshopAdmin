<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class SysDept extends Model
{
    protected $name = 'sys_dept';
    protected $pk = 'dept_id';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
}
