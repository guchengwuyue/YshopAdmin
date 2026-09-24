<?php
declare(strict_types=1);

namespace app\service\wechat;

use app\model\wechat\WechatConfig;
use app\common\PageHelper;

/**
 * wechat_config 业务 KV
 */
class ConfigService
{
    /**
     * 分页列表
     */
    public function selectList(array $params = []): array
    {
        PageHelper::startPage();
        $query = WechatConfig::order('id', 'desc');
        $name = trim((string) ($params['name'] ?? ''));
        if ($name !== '') {
            $query->whereLike('name', '%' . $name . '%');
        }
        $title = trim((string) ($params['title'] ?? ''));
        if ($title !== '') {
            $query->whereLike('title', '%' . $title . '%');
        }
        [$rows, $total] = PageHelper::paginate($query);
        $rows = PageHelper::camelRows(is_array($rows) ? $rows : $rows->toArray());
        return [$rows, $total];
    }

    public function get(int $id): ?array
    {
        $row = WechatConfig::find($id);
        return $row ? PageHelper::camelKeys($row->toArray()) : null;
    }

    public function insert(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $name = trim((string) ($data['name'] ?? ''));
        $title = trim((string) ($data['title'] ?? ''));
        if ($name === '' || $title === '') {
            throw new \InvalidArgumentException('名称与标题不能为空');
        }
        if (WechatConfig::where('name', $name)->find()) {
            throw new \RuntimeException('配置名称已存在');
        }
        $value = $this->normalizeValue($data);
        $row = WechatConfig::create([
            'name'       => $name,
            'title'      => $title,
            'value'      => $value,
            'createtime' => time(),
            'updatetime' => time(),
        ]);
        return (int) $row->id;
    }

    public function update(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $id = (int) ($data['id'] ?? 0);
        $row = WechatConfig::find($id);
        if (!$row) {
            throw new \RuntimeException('配置不存在');
        }
        $name = trim((string) ($data['name'] ?? $row->name));
        $title = trim((string) ($data['title'] ?? $row->title));
        if ($name === '' || $title === '') {
            throw new \InvalidArgumentException('名称与标题不能为空');
        }
        $dup = WechatConfig::where('name', $name)->where('id', '<>', $id)->find();
        if ($dup) {
            throw new \RuntimeException('配置名称已存在');
        }
        $row->save([
            'name'       => $name,
            'title'      => $title,
            'value'      => $this->normalizeValue($data),
            'updatetime' => time(),
        ]);
        return 1;
    }

    public function delete(string $ids): int
    {
        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        if (!$idArr) {
            return 0;
        }
        return WechatConfig::whereIn('id', $idArr)->delete();
    }

    protected function normalizeValue(array $data): string
    {
        $mode = (string) ($data['mode'] ?? 'textarea');
        if ($mode === 'json') {
            $fields = (array) ($data['field'] ?? []);
            $values = (array) ($data['value_list'] ?? ($data['json_value'] ?? []));
            $map = [];
            foreach ($fields as $k => $f) {
                $f = trim((string) $f);
                if ($f !== '') {
                    $map[$f] = (string) ($values[$k] ?? '');
                }
            }
            return json_encode($map, JSON_UNESCAPED_UNICODE);
        }
        $value = $data['value'] ?? '';
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;
    }
}
