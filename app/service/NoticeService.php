<?php
declare(strict_types=1);

namespace app\service;

use app\common\Auth;
use app\common\PageHelper;
use think\facade\Db;

/**
 * 通知公告管理
 */
class NoticeService
{
    /**
     * 查询通知公告列表
     * @param array $params 筛选条件（noticeTitle / createBy / noticeType）
     */
    public function selectList(array $params = []): array
    {
        PageHelper::startPage();
        $query = Db::name('sys_notice');
        $noticeTitle = $params['noticeTitle'] ?? $params['notice_title'] ?? '';
        if ($noticeTitle !== '') {
            $query->whereLike('notice_title', '%' . $noticeTitle . '%');
        }
        $createBy = $params['createBy'] ?? $params['create_by'] ?? '';
        if ($createBy !== '') {
            $query->whereLike('create_by', '%' . $createBy . '%');
        }
        if (isset($params['noticeType']) && $params['noticeType'] !== '') {
            $query->where('notice_type', $params['noticeType']);
        }
        [$rows, $total] = PageHelper::paginate($query);
        return [PageHelper::camelRows($rows), $total];
    }

    /**
     * 根据公告 ID 查询详情
     */
    public function get(int $noticeId): ?array
    {
        $row = Db::name('sys_notice')->where('notice_id', $noticeId)->find();
        return $row ? PageHelper::camelKeys($row) : null;
    }

    /**
     * 新增通知公告
     */
    public function insert(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $row = [
            'notice_title'   => $data['notice_title'] ?? '',
            'notice_type'    => $data['notice_type'] ?? '1',
            'notice_content' => $data['notice_content'] ?? '',
            'status'         => $data['status'] ?? '0',
            'remark'         => $data['remark'] ?? '',
            'create_by'      => Auth::getLoginName(),
            'create_time'    => date('Y-m-d H:i:s'),
        ];
        return (int) Db::name('sys_notice')->insertGetId($row);
    }

    /**
     * 修改通知公告
     */
    public function update(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $noticeId = (int) ($data['notice_id'] ?? 0);
        return Db::name('sys_notice')->where('notice_id', $noticeId)->update([
            'notice_title'   => $data['notice_title'] ?? '',
            'notice_type'    => $data['notice_type'] ?? '1',
            'notice_content' => $data['notice_content'] ?? '',
            'status'         => $data['status'] ?? '0',
            'remark'         => $data['remark'] ?? '',
            'update_by'      => Auth::getLoginName(),
            'update_time'    => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 删除通知公告
     * @param string $ids 公告 ID，逗号分隔
     */
    public function delete(string $ids): int
    {
        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        if (!$idArr) {
            return 0;
        }
        return Db::name('sys_notice')->whereIn('notice_id', $idArr)->delete();
    }
}
