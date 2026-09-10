<?php
declare(strict_types=1);

namespace app\controller\tool;

use app\controller\BaseController;
use think\facade\View;

/**
 * 表单构建
 */
class Build extends BaseController
{
    /**
     * 表单构建页
     */
    public function index()
    {
        return View::fetch('build/build');
    }
}
