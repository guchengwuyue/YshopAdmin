<?php
declare(strict_types=1);

namespace app\service;

use app\common\Auth;
use app\common\PageHelper;
use app\common\UserAgent;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;

/**
 * 在线用户
 */
class OnlineUserService
{
    /**
     * 查询在线用户列表
     * @param array $params 筛选条件（ipaddr / loginName）
     */
    public function selectList(array $params = []): array
    {
        PageHelper::startPage();
        $query = Db::name('sys_user_online');
        $ipaddr = $params['ipaddr'] ?? '';
        if ($ipaddr !== '') {
            $query->whereLike('ipaddr', '%' . $ipaddr . '%');
        }
        $loginName = $params['loginName'] ?? $params['login_name'] ?? '';
        if ($loginName !== '') {
            $query->whereLike('login_name', '%' . $loginName . '%');
        }
        if (PageHelper::getPageNum()) {
            // 默认排序
        }
        $query->order('last_access_time', 'desc');
        [$rows, $total] = PageHelper::paginate($query);
        return [PageHelper::camelRows($rows), $total];
    }

    /**
     * 写入或刷新在线用户记录
     */
    public function saveOnline(string $loginName): void
    {
        $sessionId = Session::getId();
        if ($sessionId === '') {
            return;
        }
        $ua = (string) Request::header('user-agent');
        $expire = (int) Config::get('session.expire', 1440);
        $now = date('Y-m-d H:i:s');
        $deptName = '';
        $user = Auth::getLoginUser();
        if ($user) {
            $deptId = (int) ($user['deptId'] ?? $user['dept_id'] ?? 0);
            if ($deptId > 0) {
                $deptName = (string) Db::name('sys_dept')->where('dept_id', $deptId)->value('dept_name');
            }
        }

        $exists = Db::name('sys_user_online')->where('sessionId', $sessionId)->find();
        $row = [
            'sessionId'        => $sessionId,
            'login_name'       => $loginName,
            'dept_name'        => $deptName,
            'ipaddr'           => Request::ip(),
            'login_location'   => '',
            'browser'          => UserAgent::browser($ua),
            'os'               => UserAgent::os($ua),
            'status'           => 'on_line',
            'last_access_time' => $now,
            'expire_time'      => $expire,
        ];
        if ($exists) {
            Db::name('sys_user_online')->where('sessionId', $sessionId)->update($row);
        } else {
            $row['start_timestamp'] = $now;
            Db::name('sys_user_online')->insert($row);
        }
    }

    /**
     * 刷新最后访问时间
     */
    public function touch(string $sessionId): void
    {
        if ($sessionId === '') {
            return;
        }
        try {
            Db::name('sys_user_online')->where('sessionId', $sessionId)->update([
                'last_access_time' => date('Y-m-d H:i:s'),
                'status'           => 'on_line',
            ]);
        } catch (\Throwable $e) {
        }
    }

    /**
     * 按会话 ID 移除在线记录
     */
    public function removeBySession(string $sessionId): void
    {
        if ($sessionId === '') {
            return;
        }
        Db::name('sys_user_online')->where('sessionId', $sessionId)->delete();
    }

    /**
     * 批量强退会话（跳过当前会话）
     * @param string $ids 会话 ID，逗号分隔
     * @return array{forced:int,skippedSelf:bool}
     */
    public function batchForceLogout(string $ids): array
    {
        $current = Session::getId();
        $idArr = array_filter(array_map('trim', explode(',', $ids)));
        $forced = 0;
        $skippedSelf = false;
        foreach ($idArr as $sid) {
            if ($sid === $current) {
                $skippedSelf = true;
                continue;
            }
            $this->destroySessionFile($sid);
            $this->removeBySession($sid);
            $forced++;
        }
        return ['forced' => $forced, 'skippedSelf' => $skippedSelf];
    }

    /**
     * 销毁会话文件
     */
    public function destroySessionFile(string $sessionId): void
    {
        $path = (string) Config::get('session.path', '');
        if ($path === '') {
            $path = runtime_path() . 'session';
        }
        $path = rtrim($path, DIRECTORY_SEPARATOR . '/\\');
        $candidates = [
            $path . DIRECTORY_SEPARATOR . 'sess_' . $sessionId,
            $path . DIRECTORY_SEPARATOR . $sessionId,
        ];
        // ThinkPHP 文件会话可能使用哈希前缀目录
        foreach (glob($path . DIRECTORY_SEPARATOR . '*' . $sessionId . '*') ?: [] as $f) {
            $candidates[] = $f;
        }
        foreach (array_unique($candidates) as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
