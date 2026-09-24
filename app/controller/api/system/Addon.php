<?php
declare(strict_types=1);

namespace app\controller\api\system;

use app\controller\BaseController;
use app\service\AddonService;

/**
 * 插件管理 JSON API。仅服务内容插件（yscms 及同类），不含已收编的支付/微信/文件/插件开发。
 */
class Addon extends BaseController
{
    protected AddonService $service;

    protected function initialize()
    {
        $this->service = new AddonService();
    }

    public function list()
    {
        $params = $this->request->param();
        $params['scope'] = 'content';
        [$rows, $total] = $this->service->selectList($params);
        return $this->getDataTable($rows, $total);
    }

    public function install()
    {
        try {
            return $this->toAjax($this->service->install((string) $this->request->param('name', '')));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function uninstall()
    {
        try {
            return $this->toAjax($this->service->uninstall((string) $this->request->param('name', '')));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function enable()
    {
        try {
            return $this->toAjax($this->service->enable((string) $this->request->param('name', '')));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function disable()
    {
        try {
            return $this->toAjax($this->service->disable((string) $this->request->param('name', '')));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function upload()
    {
        try {
            $file = $this->request->file('file');
            if (!$file) {
                return $this->error('请上传 zip 文件');
            }
            $tmp = $file->getRealPath() ?: $file->getPathname();
            $result = $this->service->uploadZip($tmp, true);
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

    public function config(string $name = '')
    {
        $name = $name !== '' ? $name : (string) $this->request->param('name', '');
        try {
            return $this->success('success', $this->service->getConfigForm($name));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function saveConfig()
    {
        try {
            $name = (string) $this->request->param('name', '');
            return $this->toAjax($this->service->saveConfig($name, $this->request->param()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
