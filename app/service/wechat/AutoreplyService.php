<?php
declare(strict_types=1);

namespace app\service\wechat;

use app\model\wechat\WechatAutoreply;
use app\model\wechat\WechatResponse;
use app\common\PageHelper;

/**
 * 自动回复 CRUD
 */
class AutoreplyService
{
    public function selectList(array $params = []): array
    {
        PageHelper::startPage();
        $query = WechatAutoreply::order('weigh', 'desc')->order('id', 'desc');
        $title = trim((string) ($params['title'] ?? ''));
        if ($title !== '') {
            $query->whereLike('title', '%' . $title . '%');
        }
        $text = trim((string) ($params['text'] ?? ''));
        if ($text !== '') {
            $query->whereLike('text', '%' . $text . '%');
        }
        $status = trim((string) ($params['status'] ?? ''));
        if ($status !== '') {
            $query->where('status', $status);
        }
        [$rows, $total] = PageHelper::paginate($query);
        $arr = is_array($rows) ? $rows : $rows->toArray();
        return [PageHelper::camelRows($arr), $total];
    }

    public function get(int $id): ?array
    {
        $row = WechatAutoreply::find($id);
        if (!$row) {
            return null;
        }
        $data = PageHelper::camelKeys($row->toArray());
        $response = WechatResponse::where('eventkey', $row['eventkey'])->find();
        $data['responseTitle'] = $response ? (string) $response['title'] : '';
        return $data;
    }

    public function insert(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $text = trim((string) ($data['text'] ?? ''));
        if ($text === '') {
            throw new \InvalidArgumentException('触发文本不能为空');
        }
        if (!$this->isTextUnique($text)) {
            throw new \RuntimeException('触发文本已存在');
        }
        $eventkey = trim((string) ($data['eventkey'] ?? ''));
        if ($eventkey === '') {
            throw new \InvalidArgumentException('请选择响应资源');
        }
        $row = WechatAutoreply::create([
            'title'      => trim((string) ($data['title'] ?? '')),
            'text'       => $text,
            'eventkey'   => $eventkey,
            'remark'     => (string) ($data['remark'] ?? ''),
            'weigh'      => (int) ($data['weigh'] ?? 0),
            'status'     => (string) ($data['status'] ?? 'normal'),
            'createtime' => time(),
            'updatetime' => time(),
        ]);
        return (int) $row->id;
    }

    public function update(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $id = (int) ($data['id'] ?? 0);
        $row = WechatAutoreply::find($id);
        if (!$row) {
            throw new \RuntimeException('记录不存在');
        }
        $text = trim((string) ($data['text'] ?? $row->text));
        if ($text === '') {
            throw new \InvalidArgumentException('触发文本不能为空');
        }
        if (!$this->isTextUnique($text, null, $id)) {
            throw new \RuntimeException('触发文本已存在');
        }
        $eventkey = trim((string) ($data['eventkey'] ?? $row->eventkey));
        if ($eventkey === '') {
            throw new \InvalidArgumentException('请选择响应资源');
        }
        $row->save([
            'title'      => trim((string) ($data['title'] ?? $row->title)),
            'text'       => $text,
            'eventkey'   => $eventkey,
            'remark'     => (string) ($data['remark'] ?? ''),
            'weigh'      => (int) ($data['weigh'] ?? $row->weigh),
            'status'     => (string) ($data['status'] ?? $row->status),
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
        return WechatAutoreply::whereIn('id', $idArr)->delete();
    }

    /**
     * 触发文本唯一
     */
    public function isTextUnique(string $text, ?string $except = null, int $exceptId = 0): bool
    {
        $query = WechatAutoreply::where('text', $text);
        if ($exceptId > 0) {
            $query->where('id', '<>', $exceptId);
        } elseif ($except !== null && $except !== '') {
            $query->where('text', '<>', $except);
        }
        return $query->count() === 0;
    }
}
