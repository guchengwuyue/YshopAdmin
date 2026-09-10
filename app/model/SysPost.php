<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class SysPost extends Model
{
    protected $name = 'sys_post';
    protected $pk = 'post_id';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
}
