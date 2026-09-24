<?php
declare(strict_types=1);

namespace app\service\wechat;

use app\library\wechat\response\HandlerRegistry;
use app\model\wechat\WechatResponse;
use app\common\PageHelper;

/**
 * 回复资源 CRUD
 */
class ResponseService
{
    public function selectList(array $params = []): array
    {
        PageHelper::startPage();
        $query = WechatResponse::order('id', 'desc');
        $title = trim((string) ($params['title'] ?? ''));
        if ($title !== '') {
            $query->whereLike('title', '%' . $title . '%');
        }
        $type = trim((string) ($params['type'] ?? ''));
        if ($type !== '') {
            $query->where('type', $type);
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
        $row = WechatResponse::find($id);
        if (!$row) {
            return null;
        }
        $data = PageHelper::camelKeys($row->toArray());
        $data['contentArr'] = (array) json_decode((string) $row['content'], true);
        return $data;
    }

    public function insert(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            throw new \InvalidArgumentException('资源标题不能为空');
        }
        $eventkey = trim((string) ($data['eventkey'] ?? ''));
        if ($eventkey === '') {
            $eventkey = uniqid();
        }
        if (WechatResponse::where('eventkey', $eventkey)->find()) {
            throw new \RuntimeException('事件标识已存在');
        }
        $content = $data['content'] ?? [];
        if (!is_array($content)) {
            $decoded = json_decode((string) $content, true);
            $content = is_array($decoded) ? $decoded : ['content' => (string) $content];
        }
        $row = WechatResponse::create([
            'title'      => $title,
            'eventkey'   => $eventkey,
            'type'       => (string) ($data['type'] ?? 'text'),
            'content'    => json_encode($content, JSON_UNESCAPED_UNICODE),
            'remark'     => (string) ($data['remark'] ?? ''),
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
        $row = WechatResponse::find($id);
        if (!$row) {
            throw new \RuntimeException('资源不存在');
        }
        $eventkey = trim((string) ($data['eventkey'] ?? $row->eventkey));
        if ($eventkey === '') {
            $eventkey = uniqid();
        }
        $dup = WechatResponse::where('eventkey', $eventkey)->where('id', '<>', $id)->find();
        if ($dup) {
            throw new \RuntimeException('事件标识已存在');
        }
        $content = $data['content'] ?? [];
        if (!is_array($content)) {
            $decoded = json_decode((string) $content, true);
            $content = is_array($decoded) ? $decoded : ['content' => (string) $content];
        }
        $row->save([
            'title'      => trim((string) ($data['title'] ?? $row->title)),
            'eventkey'   => $eventkey,
            'type'       => (string) ($data['type'] ?? $row->type),
            'content'    => json_encode($content, JSON_UNESCAPED_UNICODE),
            'remark'     => (string) ($data['remark'] ?? ''),
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
        return WechatResponse::whereIn('id', $idArr)->delete();
    }

    /**
     * 扩展 app 配置
     */
    public function appConfig(): array
    {
        return HandlerRegistry::appConfig();
    }
}
