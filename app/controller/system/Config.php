<?php
declare(strict_types=1);

namespace app\controller\system;

use app\controller\BaseController;
use app\service\ConfigService;
use think\facade\View;

/**
 * 参数配置管理
 */
class Config extends BaseController
{
    protected ConfigService $configService;

    protected function initialize()
    {
        $this->configService = new ConfigService();
    }

    /**
     * 参数配置列表页
     */
    public function index()
    {
        return View::fetch('config/config');
    }

    /**
     * 查询参数配置列表
     */
    public function list()
    {
        [$rows, $total] = $this->configService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 新增参数配置
     */
    public function add()
    {
        if ($this->request->isPost()) {
            try {
                return $this->toAjax($this->configService->insert($this->request->post()) > 0 ? 1 : 0);
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        return View::fetch('config/add');
    }

    /**
     * 修改参数配置
     * @param int $id 参数 ID
     */
    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            return $this->toAjax($this->configService->update($this->request->post()));
        }
        return View::fetch('config/edit', ['config' => $this->configService->get((int) $id)]);
    }

    /**
     * 删除参数配置
     */
    public function remove()
    {
        return $this->toAjax($this->configService->delete((string) $this->request->post('ids', '')));
    }

    /**
     * 校验参数键名是否唯一
     */
    public function checkConfigKeyUnique()
    {
        $ok = $this->configService->checkConfigKeyUnique(
            (string) $this->request->param('configKey', ''),
            $this->request->param('configId') ? (int) $this->request->param('configId') : null
        );
        return $ok ? 'true' : 'false';
    }

    /**
     * 导出参数配置
     */
    public function export()
    {
        [$rows] = $this->configService->selectList($this->request->param(), true);
        $file = \app\common\ExcelUtil::export($rows, [
            'configId' => 'Config ID',
            'configName' => 'Name',
            'configKey' => 'Key',
            'configValue' => 'Value',
            'configType' => 'Type',
            'createTime' => 'Create Time',
        ], 'config');
        return $this->success($file);
    }
}
