<?php
declare(strict_types=1);

namespace app\controller\system;

use app\controller\BaseController;
use app\service\AddonService;
use think\facade\View;

/**
 * 插件管理
 */
class Addon extends BaseController
{
    protected AddonService $addonService;

    protected function initialize()
    {
        $this->addonService = new AddonService();
    }

    /**
     * 列表页
     */
    public function index()
    {
        return View::fetch('addon/addon');
    }

    /**
     * 分页列表
     */
    public function list()
    {
        [$rows, $total] = $this->addonService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 安装
     */
    public function install()
    {
        try {
            $name = (string) $this->request->post('name', '');
            return $this->toAjax($this->addonService->install($name));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 卸载
     */
    public function uninstall()
    {
        try {
            $name = (string) $this->request->post('name', '');
            return $this->toAjax($this->addonService->uninstall($name));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 启用
     */
    public function enable()
    {
        try {
            $name = (string) $this->request->post('name', '');
            return $this->toAjax($this->addonService->enable($name));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 禁用
     */
    public function disable()
    {
        try {
            $name = (string) $this->request->post('name', '');
            return $this->toAjax($this->addonService->disable($name));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 本地 zip 安装/升级
     */
    public function upload()
    {
        try {
            $file = $this->request->file('file');
            if (!$file) {
                return $this->error('请上传 zip 文件');
            }
            $tmp = $file->getRealPath() ?: $file->getPathname();
            $result = $this->addonService->uploadZip($tmp, true);
            if (($result['action'] ?? '') === 'upgrade') {
                return $this->success(sprintf(
                    '升级成功: %s (%s → %s)',
                    $result['name'] ?? '',
                    $result['from'] ?? '',
                    $result['to'] ?? ''
                ));
            }
            return $this->success('安装成功: ' . ($result['name'] ?? ''));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 配置
     */
    public function config(string $name = '')
    {
        $name = $name !== '' ? $name : (string) $this->request->param('name', '');
        if ($this->request->isPost()) {
            try {
                return $this->toAjax($this->addonService->saveConfig($name, $this->request->post()));
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        $form = $this->addonService->getConfigForm($name);
        return View::fetch('addon/config', $form);
    }
}
