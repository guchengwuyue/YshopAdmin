<?php
declare(strict_types=1);

namespace app\controller\api\wechat;

use app\service\wechat\ResponseService;
use app\controller\BaseController;

/**
 * 微信资源 JSON API
 */
class Response extends BaseController
{
    protected ResponseService $service;

    protected function initialize()
    {
        $this->service = new ResponseService();
    }

    public function list()
    {
        [$rows, $total] = $this->service->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    public function get($id = 0)
    {
        $row = $this->service->get((int) ($id ?: $this->request->param('id', 0)));
        if (!$row) {
            return $this->error('资源不存在');
        }
        return $this->success('success', $row);
    }

    public function add()
    {
        try {
            return $this->toAjax($this->service->insert($this->payload()) > 0 ? 1 : 0);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function edit()
    {
        try {
            return $this->toAjax($this->service->update($this->payload()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function remove()
    {
        try {
            return $this->toAjax($this->service->delete((string) $this->request->param('ids', '')));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 表单 content 文本转资源 content 数组
     */
    private function payload(): array
    {
        $data = $this->request->param();
        if (!isset($data['content']) && isset($data['contentText'])) {
            $text = (string) $data['contentText'];
            $decoded = json_decode($text, true);
            $data['content'] = is_array($decoded) ? $decoded : ['content' => $text];
        }
        return $data;
    }
}
