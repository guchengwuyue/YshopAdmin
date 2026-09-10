<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class SysDictData extends Model
{
    protected $name = 'sys_dict_data';
    protected $pk = 'dict_code';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
}
