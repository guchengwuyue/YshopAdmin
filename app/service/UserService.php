<?php
declare(strict_types=1);

namespace app\service;

use app\common\Auth;
use app\common\DataScope;
use app\common\PageHelper;
use app\common\PasswordUtils;
use think\facade\Db;

/**
 * 用户管理
 */
class UserService
{
    /**
     * 查询用户列表
     * @param array $params 筛选条件（loginName / phonenumber / status / deptId / beginTime / endTime）
     * @param bool $export 是否导出（不分页）
     */
    public function selectList(array $params = [], bool $export = false): array
    {
        if ($export) {
            PageHelper::startExport();
        } else {
            PageHelper::startPage();
        }
        $query = Db::name('sys_user')->alias('u')
            ->leftJoin('sys_dept d', 'u.dept_id = d.dept_id')
            ->where('u.del_flag', '0')
            ->field('u.user_id,u.dept_id,u.login_name,u.user_name,u.email,u.phonenumber,u.sex,u.avatar,u.status,u.login_ip,u.login_date,u.create_by,u.create_time,u.remark,d.dept_name,d.leader');

        DataScope::applyDataScope($query, 'u', 'd');

        $loginName = $params['loginName'] ?? $params['login_name'] ?? '';
        if ($loginName !== '') {
            $query->whereLike('u.login_name', '%' . $loginName . '%');
        }
        $phonenumber = $params['phonenumber'] ?? '';
        if ($phonenumber !== '') {
            $query->whereLike('u.phonenumber', '%' . $phonenumber . '%');
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('u.status', $params['status']);
        }
        $deptId = $params['deptId'] ?? $params['dept_id'] ?? '';
        if ($deptId !== '' && $deptId !== null) {
            $query->where(function ($q) use ($deptId) {
                $q->where('u.dept_id', $deptId)
                    ->whereOrRaw("u.dept_id IN (SELECT t.dept_id FROM sys_dept t WHERE FIND_IN_SET(?, ancestors))", [$deptId]);
            });
        }
        $beginTime = $params['beginTime'] ?? ($params['params']['beginTime'] ?? '');
        $endTime = $params['endTime'] ?? ($params['params']['endTime'] ?? '');
        if ($beginTime !== '') {
            $query->where('u.create_time', '>=', $beginTime);
        }
        if ($endTime !== '') {
            $query->where('u.create_time', '<=', $endTime . ' 23:59:59');
        }

        [$rows, $total] = PageHelper::paginate($query);
        foreach ($rows as &$row) {
            $row['dept'] = [
                'deptId'   => $row['dept_id'] ?? null,
                'deptName' => $row['dept_name'] ?? null,
                'leader'   => $row['leader'] ?? null,
            ];
        }
        unset($row);
        return [PageHelper::camelRows($rows), $total];
    }

    /**
     * 根据用户 ID 查询详情（含角色、岗位）
     */
    public function get(int $userId): ?array
    {
        $row = Db::name('sys_user')->alias('u')
            ->leftJoin('sys_dept d', 'u.dept_id = d.dept_id')
            ->where('u.user_id', $userId)
            ->where('u.del_flag', '0')
            ->field('u.*,d.dept_name')
            ->find();
        if (!$row) {
            return null;
        }
        unset($row['password'], $row['salt']);
        $user = PageHelper::camelKeys($row);
        $user['roleIds'] = Db::name('sys_user_role')->where('user_id', $userId)->column('role_id');
        $user['postIds'] = Db::name('sys_user_post')->where('user_id', $userId)->column('post_id');
        $user['dept'] = ['deptId' => $row['dept_id'], 'deptName' => $row['dept_name'] ?? ''];
        return $user;
    }

    /**
     * 新增用户
     */
    public function insert(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $loginName = (string) ($data['login_name'] ?? '');
        if (!$this->checkLoginNameUnique($loginName)) {
            throw new \RuntimeException('登录账号已存在');
        }
        $salt = PasswordUtils::randomSalt();
        $password = (string) ($data['password'] ?? 'admin123');
        $roleIds = $this->parseIds($data['roleIds'] ?? $data['role_ids'] ?? []);
        $postIds = $this->parseIds($data['postIds'] ?? $data['post_ids'] ?? []);

        $row = [
            'dept_id'     => $data['dept_id'] ?? null,
            'login_name'  => $loginName,
            'user_name'   => $data['user_name'] ?? '',
            'email'       => $data['email'] ?? '',
            'phonenumber' => $data['phonenumber'] ?? '',
            'sex'         => $data['sex'] ?? '0',
            'password'    => PasswordUtils::encryptPassword($loginName, $password, $salt),
            'salt'        => $salt,
            'status'      => $data['status'] ?? '0',
            'del_flag'    => '0',
            'remark'      => $data['remark'] ?? '',
            'create_by'   => Auth::getLoginName(),
            'create_time' => date('Y-m-d H:i:s'),
            'pwd_update_date' => date('Y-m-d H:i:s'),
        ];
        $userId = (int) Db::name('sys_user')->insertGetId($row);
        $this->insertUserRole($userId, $roleIds);
        $this->insertUserPost($userId, $postIds);
        return $userId;
    }

    /**
     * 修改用户
     */
    public function update(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $userId = (int) ($data['user_id'] ?? 0);
        if ($userId === 1 && Auth::getUserId() !== 1) {
            throw new \RuntimeException('不允许操作超级管理员用户');
        }
        $roleIds = $this->parseIds($data['roleIds'] ?? $data['role_ids'] ?? null);
        $postIds = $this->parseIds($data['postIds'] ?? $data['post_ids'] ?? null);

        $row = [
            'dept_id'     => $data['dept_id'] ?? null,
            'user_name'   => $data['user_name'] ?? '',
            'email'       => $data['email'] ?? '',
            'phonenumber' => $data['phonenumber'] ?? '',
            'sex'         => $data['sex'] ?? '0',
            'status'      => $data['status'] ?? '0',
            'remark'      => $data['remark'] ?? '',
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ];
        $n = Db::name('sys_user')->where('user_id', $userId)->update($row);
        if ($roleIds !== null) {
            Db::name('sys_user_role')->where('user_id', $userId)->delete();
            $this->insertUserRole($userId, $roleIds);
        }
        if ($postIds !== null) {
            Db::name('sys_user_post')->where('user_id', $userId)->delete();
            $this->insertUserPost($userId, $postIds);
        }
        return $n;
    }

    /**
     * 删除用户（逻辑删除）
     * @param string $ids 用户 ID，逗号分隔
     */
    public function delete(string $ids): int
    {
        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        $idArr = array_values(array_filter($idArr, static fn($id) => $id !== 1));
        if (!$idArr) {
            return 0;
        }
        Db::name('sys_user_role')->whereIn('user_id', $idArr)->delete();
        Db::name('sys_user_post')->whereIn('user_id', $idArr)->delete();
        return Db::name('sys_user')->whereIn('user_id', $idArr)->update([
            'del_flag'    => '2',
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 重置用户密码
     */
    public function resetPwd(int $userId, string $password): int
    {
        $user = Db::name('sys_user')->where('user_id', $userId)->find();
        if (!$user) {
            throw new \RuntimeException('用户不存在');
        }
        $salt = PasswordUtils::randomSalt();
        return Db::name('sys_user')->where('user_id', $userId)->update([
            'salt'            => $salt,
            'password'        => PasswordUtils::encryptPassword($user['login_name'], $password, $salt),
            'pwd_update_date' => date('Y-m-d H:i:s'),
            'update_by'       => Auth::getLoginName(),
            'update_time'     => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 校验登录账号是否唯一
     * @param int|null $userId 排除的用户 ID（修改时用）
     */
    public function checkLoginNameUnique(string $loginName, ?int $userId = null): bool
    {
        $query = Db::name('sys_user')->where('login_name', $loginName)->where('del_flag', '0');
        if ($userId) {
            $query->where('user_id', '<>', $userId);
        }
        return $query->count() === 0;
    }

    /**
     * 修改用户状态
     */
    public function changeStatus(int $userId, string $status): int
    {
        if ($userId === 1) {
            throw new \RuntimeException('不允许操作超级管理员用户');
        }
        return Db::name('sys_user')->where('user_id', $userId)->update([
            'status'      => $status,
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 修改当前登录用户个人信息
     */
    public function updateProfile(array $data): int
    {
        $userId = Auth::getUserId();
        $data = PageHelper::snakeParams($data);
        return Db::name('sys_user')->where('user_id', $userId)->update([
            'user_name'   => $data['user_name'] ?? '',
            'email'       => $data['email'] ?? '',
            'phonenumber' => $data['phonenumber'] ?? '',
            'sex'         => $data['sex'] ?? '0',
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 修改当前登录用户密码
     */
    public function updatePwd(string $oldPassword, string $newPassword): int
    {
        $user = Db::name('sys_user')->where('user_id', Auth::getUserId())->find();
        if (!$user) {
            throw new \RuntimeException('用户不存在');
        }
        if (!PasswordUtils::matches($user['login_name'], $oldPassword, $user['salt'], $user['password'])) {
            throw new \RuntimeException('修改密码失败，旧密码错误');
        }
        $salt = PasswordUtils::randomSalt();
        return Db::name('sys_user')->where('user_id', $user['user_id'])->update([
            'salt'            => $salt,
            'password'        => PasswordUtils::encryptPassword($user['login_name'], $newPassword, $salt),
            'pwd_update_date' => date('Y-m-d H:i:s'),
            'update_time'     => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 导入用户数据
     * @param array $rows 导入行数据
     * @param bool $updateSupport 是否更新已存在用户
     */
    public function importRows(array $rows, bool $updateSupport = false): string
    {
        $success = 0;
        $failure = 0;
        $msgs = [];
        $initPwd = (new ConfigService())->getKey('sys.user.initPassword', 'admin123');
        foreach ($rows as $i => $row) {
            $line = $i + 2;
            try {
                $loginName = (string) ($row['loginName'] ?? $row['login_name'] ?? '');
                if ($loginName === '') {
                    throw new \RuntimeException('loginName empty');
                }
                $exists = Db::name('sys_user')->where('login_name', $loginName)->where('del_flag', '0')->find();
                if ($exists) {
                    if (!$updateSupport) {
                        throw new \RuntimeException('user exists');
                    }
                    $this->update([
                        'userId' => $exists['user_id'],
                        'userName' => $row['userName'] ?? $row['user_name'] ?? $exists['user_name'],
                        'email' => $row['email'] ?? $exists['email'],
                        'phonenumber' => $row['phonenumber'] ?? $exists['phonenumber'],
                        'sex' => $row['sex'] ?? $exists['sex'],
                        'status' => $row['status'] ?? $exists['status'],
                        'deptId' => $row['deptId'] ?? $row['dept_id'] ?? $exists['dept_id'],
                    ]);
                } else {
                    $this->insert([
                        'loginName' => $loginName,
                        'userName' => $row['userName'] ?? $row['user_name'] ?? $loginName,
                        'password' => $initPwd,
                        'email' => $row['email'] ?? '',
                        'phonenumber' => $row['phonenumber'] ?? '',
                        'sex' => $row['sex'] ?? '0',
                        'status' => $row['status'] ?? '0',
                        'deptId' => $row['deptId'] ?? $row['dept_id'] ?? null,
                    ]);
                }
                $success++;
            } catch (\Throwable $e) {
                $failure++;
                $msgs[] = "row {$line}: " . $e->getMessage();
            }
        }
        return "success {$success}, failure {$failure}" . ($msgs ? (': ' . implode('; ', array_slice($msgs, 0, 5))) : '');
    }

    /**
     * 写入用户角色关联
     */
    protected function insertUserRole(int $userId, array $roleIds): void
    {
        foreach ($roleIds as $roleId) {
            Db::name('sys_user_role')->insert(['user_id' => $userId, 'role_id' => (int) $roleId]);
        }
    }

    /**
     * 写入用户岗位关联
     */
    protected function insertUserPost(int $userId, array $postIds): void
    {
        foreach ($postIds as $postId) {
            Db::name('sys_user_post')->insert(['user_id' => $userId, 'post_id' => (int) $postId]);
        }
    }

    /**
     * 解析 ID 列表（支持数组或逗号分隔字符串；null 表示未传）
     */
    protected function parseIds($ids): ?array
    {
        if ($ids === null) {
            return null;
        }
        if (is_string($ids)) {
            $ids = $ids === '' ? [] : explode(',', $ids);
        }
        if (!is_array($ids)) {
            return [];
        }
        return array_values(array_filter(array_map('intval', $ids)));
    }
}
