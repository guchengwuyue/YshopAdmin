<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class SysDictType extends Model
{
    protected $name = 'sys_dict_type';
    protected $pk = 'dict_id';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
}
