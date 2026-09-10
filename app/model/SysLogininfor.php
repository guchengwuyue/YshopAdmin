<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class SysLogininfor extends Model
{
    protected $name = 'sys_logininfor';
    protected $pk = 'info_id';
    protected $autoWriteTimestamp = false;
}
