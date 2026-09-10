<?php
declare(strict_types=1);

namespace app\controller;

use app\BaseController as AppBaseController;
use app\common\AjaxResult;
use app\common\Auth;
use app\common\TableDataInfo;
use think\Response;

/**
 * 业务控制器基类
 */
abstract class BaseController extends AppBaseController
{
    /**
     * 成功响应
     */
    protected function success(string $msg = '操作成功', $data = null): Response
    {
        return AjaxResult::success($msg, $data);
    }

    /**
     * 失败响应
     */
    protected function error(string $msg = '操作失败', int $code = 500): Response
    {
        return AjaxResult::error($msg, $code);
    }

    /**
     * 表格分页数据响应
     * @param array $list 当前页数据
     * @param int $total 总记录数
     */
    protected function getDataTable(array $list, int $total): Response
    {
        return TableDataInfo::build($list, $total);
    }

    /**
     * 按影响行数返回成功/失败
     * @param int $rows 影响行数
     */
    protected function toAjax(int $rows): Response
    {
        return $rows > 0 ? $this->success() : $this->error();
    }

    /**
     * 当前登录用户
     */
    protected function loginUser(): ?array
    {
        return Auth::getLoginUser();
    }

    /**
     * 当前用户 ID
     */
    protected function getUserId(): int
    {
        return Auth::getUserId();
    }

    /**
     * 当前登录账号
     */
    protected function getLoginName(): string
    {
        return Auth::getLoginName();
    }
}
