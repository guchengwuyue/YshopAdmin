<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class SysMenu extends Model
{
    protected $name = 'sys_menu';
    protected $pk = 'menu_id';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
}
