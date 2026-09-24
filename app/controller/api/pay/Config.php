<?php
declare(strict_types=1);

namespace app\controller\api\pay;

use app\common\BuiltinConfig;
use app\controller\BaseController;
use app\service\pay\ConfigService;

/**
 * 支付配置 JSON API
 */
class Config extends BaseController
{
    protected ConfigService $service;

    protected function initialize()
    {
        $this->service = new ConfigService();
    }

    public function index()
    {
        return $this->success('success', $this->service->load());
    }

    public function save()
    {
        try {
            $post = $this->request->param('config', $this->request->param());
            if (!is_array($post)) {
                $post = [];
            }
            $this->service->save($post);
            return $this->success('保存成功');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function upload()
    {
        $file = $this->request->file('file');
        if (!$file) {
            return $this->error('请上传证书文件');
        }
        $dir = BuiltinConfig::certsDir();
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $certname = preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $this->request->param('certname', '')) ?: '';
        $ext = strtolower(pathinfo((string) $file->getOriginalName(), PATHINFO_EXTENSION));
        if (!in_array($ext, ['pem', 'crt', 'cer', 'key'], true)) {
            $ext = 'pem';
        }
        $name = $certname !== ''
            ? ($certname . '.' . $ext)
            : (preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getOriginalName()) ?: ('cert_' . time() . '.' . $ext));
        $file->move($dir, $name);
        return $this->success('上传成功', ['path' => '/data/certs/pay/' . $name]);
    }
}
