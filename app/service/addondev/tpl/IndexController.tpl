<?php
declare(strict_types=1);

namespace addons\{$name}\controller;

use addons\{$name}\service\IndexService;
use app\controller\BaseController;

/**
 * {$title}（JSON only）
 */
class Index extends BaseController
{
    protected IndexService $service;

    protected function initialize()
    {
        $this->service = new IndexService();
    }

    /**
     * 首页元数据
     */
    public function index()
    {
        return $this->success('success', [
            'msg' => $this->service->hello(),
        ]);
    }

    /**
     * 列表占位
     */
    public function list()
    {
        return $this->getDataTable([], 0);
    }
}
