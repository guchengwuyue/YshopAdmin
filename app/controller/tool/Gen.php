<?php
declare(strict_types=1);

namespace app\controller\tool;

use app\common\ExcelUtil;
use app\controller\BaseController;
use app\service\GenService;
use think\facade\View;

/**
 * 代码生成
 */
class Gen extends BaseController
{
    protected GenService $service;

    protected function initialize()
    {
        $this->service = new GenService();
    }

    /**
     * 代码生成列表页
     */
    public function index()
    {
        return View::fetch('gen/gen');
    }

    /**
     * 查询已导入表列表
     */
    public function list()
    {
        [$rows, $total] = $this->service->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 查询数据库表列表（待导入）
     */
    public function dbList()
    {
        [$rows, $total] = $this->service->selectDbTableList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 导入表结构
     */
    public function importTable()
    {
        try {
            $n = $this->service->importTable((string) $this->request->post('tables', ''));
            return $n > 0 ? $this->success() : $this->error('import failed');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 修改生成配置
     * @param int $id 表 ID
     */
    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            try {
                return $this->toAjax($this->service->update($this->request->post()));
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        return View::fetch('gen/edit', ['table' => $this->service->get((int) $id)]);
    }

    /**
     * 删除已导入表
     */
    public function remove()
    {
        return $this->toAjax($this->service->delete((string) $this->request->post('ids', '')));
    }

    /**
     * 预览生成代码
     * @param int $id 表 ID
     */
    public function preview($id = 0)
    {
        try {
            $files = $this->service->preview((int) $id);
            return json(['code' => 0, 'msg' => 'success', 'data' => $files]);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 下载生成代码 zip
     * @param string $id 表 ID（可逗号分隔）
     */
    public function download($id = '')
    {
        try {
            $tableIds = (string) ($id !== '' && $id !== null ? $id : $this->request->param('tableIds', $this->request->param('tables', '')));
            if ($tableIds === '') {
                $tableIds = (string) $this->request->post('tables', '');
            }
            $file = $this->service->downloadZip($tableIds);
            return download(ExcelUtil::downloadDir() . $file, $file);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 批量生成并下载
     */
    public function batchGenCode()
    {
        try {
            $tables = (string) $this->request->param('tables', '');
            $file = $this->service->downloadZip($tables);
            return download(ExcelUtil::downloadDir() . $file, $file);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
