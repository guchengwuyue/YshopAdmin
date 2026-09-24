<?php
declare(strict_types=1);

namespace app\service\wechat;

use app\model\wechat\WechatConfig;
use app\model\wechat\WechatResponse;
use app\service\wechat\OfficialAccountFactory;

/**
 * 公众号菜单
 */
class MenuService
{
    /**
     * 读取本地菜单与资源标题映射
     * @return array{menu:array,responseList:array}
     */
    public function getEditorData(): array
    {
        $cfg = $this->getMenuRow();
        $menu = (array) json_decode((string) ($cfg['value'] ?? '[]'), true);
        $responseList = [];
        foreach (WechatResponse::select() as $v) {
            $responseList[(string) $v['eventkey']] = (string) $v['title'];
        }
        return ['menu' => $menu, 'responseList' => $responseList, 'configId' => (int) ($cfg['id'] ?? 0)];
    }

    /**
     * 保存本地菜单 JSON
     */
    public function save(array $menu): void
    {
        $menu = $this->sanitizeButtons($menu);
        $cfg = $this->getMenuRow();
        $cfg->value = json_encode($menu, JSON_UNESCAPED_UNICODE);
        $cfg->save();
    }

    /**
     * 从微信拉取菜单
     */
    public function pullRemote(): void
    {
        $app = OfficialAccountFactory::make();
        $list = $app->menu->list();
        if (isset($list['menu']['button'])) {
            $buttons = $list['menu']['button'];
            foreach ($buttons as &$item) {
                if (isset($item['sub_button'])) {
                    if ($item['sub_button']) {
                        foreach ($item['sub_button'] as &$value) {
                            if (empty($value['sub_button'])) {
                                unset($value['sub_button']);
                            }
                        }
                        unset($value);
                    } else {
                        unset($item['sub_button']);
                    }
                }
            }
            unset($item);
            $cfg = $this->getMenuRow();
            $cfg->value = json_encode($buttons, JSON_UNESCAPED_UNICODE);
            $cfg->save();
            return;
        }
        if (isset($list['errcode']) && (string) $list['errcode'] === '46003') {
            throw new \RuntimeException('服务端菜单为空');
        }
        throw new \RuntimeException('加载菜单失败');
    }

    /**
     * 同步本地菜单到微信
     */
    public function pushSync(): void
    {
        $cfg = $this->getMenuRow();
        $menu = json_decode((string) $cfg->value, true);
        if (!is_array($menu)) {
            throw new \InvalidArgumentException('菜单数据无效');
        }
        foreach ($menu as $v) {
            if (isset($v['sub_button'])) {
                foreach ($v['sub_button'] as $n) {
                    if (($n['type'] ?? '') === 'click' && empty($n['key'])) {
                        throw new \InvalidArgumentException('存在未绑定资源的 click 菜单');
                    }
                }
            } elseif (($v['type'] ?? '') === 'click' && empty($v['key'])) {
                throw new \InvalidArgumentException('存在未绑定资源的 click 菜单');
            }
        }
        $app = OfficialAccountFactory::make();
        $ret = $app->menu->create($menu);
        if (!isset($ret['errcode']) || (int) $ret['errcode'] !== 0) {
            throw new \RuntimeException((string) ($ret['errmsg'] ?? '同步失败'));
        }
    }

    /**
     * 清洗按钮字段
     */
    public function sanitizeButtons(array $menu): array
    {
        foreach ($menu as &$item) {
            if (isset($item['sub_button']) && is_array($item['sub_button'])) {
                foreach ($item['sub_button'] as &$subitem) {
                    $subitem = $this->filterButton($subitem);
                }
                unset($subitem);
                $item = [
                    'name'       => (string) ($item['name'] ?? ''),
                    'sub_button' => $item['sub_button'],
                ];
            } else {
                $item = $this->filterButton($item);
            }
        }
        unset($item);
        return $menu;
    }

    protected function filterButton(array $item): array
    {
        $type = (string) ($item['type'] ?? 'click');
        if ($type === 'view') {
            return ['type' => 'view', 'name' => (string) ($item['name'] ?? ''), 'url' => (string) ($item['url'] ?? '')];
        }
        if ($type === 'miniprogram') {
            return [
                'type'     => 'miniprogram',
                'name'     => (string) ($item['name'] ?? ''),
                'url'      => (string) ($item['url'] ?? ''),
                'appid'    => (string) ($item['appid'] ?? ''),
                'pagepath' => (string) ($item['pagepath'] ?? ''),
            ];
        }
        return ['type' => $type, 'name' => (string) ($item['name'] ?? ''), 'key' => (string) ($item['key'] ?? '')];
    }

    protected function getMenuRow(): WechatConfig
    {
        $cfg = WechatConfig::where('name', 'menu')->find();
        if (!$cfg) {
            $cfg = WechatConfig::create([
                'name'       => 'menu',
                'title'      => '公众号菜单',
                'value'      => '[]',
                'createtime' => time(),
                'updatetime' => time(),
            ]);
        }
        return $cfg;
    }
}
