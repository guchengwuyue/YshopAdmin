<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class SysOperLog extends Model
{
    protected $name = 'sys_oper_log';
    protected $pk = 'oper_id';
    protected $autoWriteTimestamp = false;
}
