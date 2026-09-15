<?php
namespace app;

use app\common\Entry;

/**
 * 应用请求对象类
 */
class Request extends \think\Request
{
    /**
     * Admin entry forces /yshopadmin root (SCRIPT_FILENAME is faked as index.php
     * for MultiApp, so auto-detected root would otherwise be empty).
     */
    public function root(bool $complete = false): string
    {
        if (Entry::isAdmin()) {
            $this->root = Entry::adminRoot();
        }

        return parent::root($complete);
    }
}
