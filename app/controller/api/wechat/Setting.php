<?php
declare(strict_types=1);

namespace app\controller\api\wechat;

use app\controller\BaseController;
use app\service\wechat\SettingService;

/**
 * 公众号设置 JSON API
 */
class Setting extends BaseController
{
    public function index()
    {
        $data = (new SettingService())->load((string) $this->request->domain());
        return $this->success('success', $data);
    }

    public function save()
    {
        try {
            $post = $this->request->param('config', $this->request->param());
            if (!is_array($post)) {
                $post = [];
            }
            (new SettingService())->save($post);
            return $this->success('保存成功');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
