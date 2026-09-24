<?php
declare(strict_types=1);

namespace app\service\file;

use app\library\file\StorageFactory;
use app\common\Auth;
use app\common\PageHelper;
use think\facade\Db;

/**
 * 文件存储配置
 */
class ConfigService
{
    /**
     * 查询配置列表
     */
    public function selectList(array $params = []): array
    {
        PageHelper::startPage();
        $query = Db::name('ys_file_config')->where('del_flag', '0');
        $name = (string) ($params['name'] ?? '');
        if ($name !== '') {
            $query->whereLike('name', '%' . $name . '%');
        }
        $storage = (string) ($params['storage'] ?? '');
        if ($storage !== '') {
            $query->where('storage', $storage);
        }
        $beginTime = $params['beginTime'] ?? ($params['params']['beginTime'] ?? '');
        $endTime = $params['endTime'] ?? ($params['params']['endTime'] ?? '');
        if ($beginTime !== '') {
            $query->where('create_time', '>=', $beginTime);
        }
        if ($endTime !== '') {
            $query->where('create_time', '<=', $endTime . ' 23:59:59');
        }
        $query->order('id', 'desc');
        [$rows, $total] = PageHelper::paginate($query);
        $rows = PageHelper::camelRows($rows);
        foreach ($rows as &$row) {
            unset($row['accessSecret']);
        }
        unset($row);
        return [$rows, $total];
    }

    /**
     * 详情（编辑回填；密钥脱敏）
     */
    public function get(int $id, bool $maskSecret = true): ?array
    {
        $row = Db::name('ys_file_config')->where('id', $id)->where('del_flag', '0')->find();
        if (!$row) {
            return null;
        }
        $data = PageHelper::camelKeys($row);
        if ($maskSecret) {
            $data['accessSecret'] = '';
            $data['hasSecret'] = ($row['access_secret'] ?? '') !== '';
        }
        return $data;
    }

    /**
     * 原始行（含密钥，供驱动使用）
     */
    public function getRaw(int $id): ?array
    {
        $row = Db::name('ys_file_config')->where('id', $id)->where('del_flag', '0')->find();
        return $row ?: null;
    }

    /**
     * 主配置原始行
     */
    public function getMasterRaw(): ?array
    {
        $row = Db::name('ys_file_config')
            ->where('del_flag', '0')
            ->where('master', '1')
            ->find();
        return $row ?: null;
    }

    /**
     * 新增
     */
    public function insert(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $storage = strtolower((string) ($data['storage'] ?? 'local'));
        if (!in_array($storage, ['local', 's3'], true)) {
            throw new \InvalidArgumentException('存储器仅支持 local / s3');
        }
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('配置名不能为空');
        }
        $this->assertS3Fields($storage, $data, true);

        $row = [
            'name'          => $name,
            'storage'       => $storage,
            'remark'        => (string) ($data['remark'] ?? ''),
            'endpoint'      => (string) ($data['endpoint'] ?? ($storage === 'local' ? 'public/storage' : '')),
            'bucket'        => (string) ($data['bucket'] ?? ''),
            'access_key'    => (string) ($data['access_key'] ?? ''),
            'access_secret' => (string) ($data['access_secret'] ?? ''),
            'domain'        => (string) ($data['domain'] ?? ''),
            'master'        => '0',
            'create_by'     => Auth::getLoginName(),
            'create_time'   => date('Y-m-d H:i:s'),
            'del_flag'      => '0',
        ];
        return (int) Db::name('ys_file_config')->insertGetId($row);
    }

    /**
     * 修改
     */
    public function update(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $id = (int) ($data['id'] ?? 0);
        $exist = $this->getRaw($id);
        if (!$exist) {
            throw new \RuntimeException('配置不存在');
        }
        $storage = strtolower((string) ($data['storage'] ?? $exist['storage']));
        if (!in_array($storage, ['local', 's3'], true)) {
            throw new \InvalidArgumentException('存储器仅支持 local / s3');
        }
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('配置名不能为空');
        }
        $secret = (string) ($data['access_secret'] ?? '');
        if ($secret === '') {
            $data['access_secret'] = (string) $exist['access_secret'];
        }
        $this->assertS3Fields($storage, array_merge($exist, $data), false);

        return Db::name('ys_file_config')->where('id', $id)->update([
            'name'          => $name,
            'storage'       => $storage,
            'remark'        => (string) ($data['remark'] ?? ''),
            'endpoint'      => (string) ($data['endpoint'] ?? ''),
            'bucket'        => (string) ($data['bucket'] ?? ''),
            'access_key'    => (string) ($data['access_key'] ?? ''),
            'access_secret' => (string) $data['access_secret'],
            'domain'        => (string) ($data['domain'] ?? ''),
            'update_by'     => Auth::getLoginName(),
            'update_time'   => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 软删（主配置不可删）
     */
    public function delete(string $ids): int
    {
        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        if (!$idArr) {
            return 0;
        }
        $masters = Db::name('ys_file_config')
            ->whereIn('id', $idArr)
            ->where('del_flag', '0')
            ->where('master', '1')
            ->count();
        if ($masters > 0) {
            throw new \RuntimeException('主配置不可删除，请先切换主配置');
        }
        return Db::name('ys_file_config')->whereIn('id', $idArr)->where('del_flag', '0')->update([
            'del_flag'    => '2',
            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 设为主配置
     */
    public function setMaster(int $id): int
    {
        $exist = $this->getRaw($id);
        if (!$exist) {
            throw new \RuntimeException('配置不存在');
        }
        Db::startTrans();
        try {
            Db::name('ys_file_config')->where('del_flag', '0')->where('master', '1')->update([
                'master'      => '0',
                'update_by'   => Auth::getLoginName(),
                'update_time' => date('Y-m-d H:i:s'),
            ]);
            $n = Db::name('ys_file_config')->where('id', $id)->update([
                'master'      => '1',
                'update_by'   => Auth::getLoginName(),
                'update_time' => date('Y-m-d H:i:s'),
            ]);
            Db::commit();
            return $n;
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
    }

    /**
     * 连通测试（写/读/删临时对象）
     * @param array|null $config 原始配置行；null 时按 id 读取
     */
    public function testConnection(?array $config = null, int $id = 0): void
    {
        if ($config === null) {
            $config = $this->getRaw($id);
        }
        if (!$config) {
            throw new \RuntimeException('配置不存在');
        }
        $driver = StorageFactory::make($config);
        $path = '_ysfile_probe_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.txt';
        $payload = 'ysfile-probe-' . time();
        $driver->put($path, $payload);
        if (!$driver->exists($path)) {
            throw new \RuntimeException('写入后读取校验失败');
        }
        $driver->delete($path);
    }

    /**
     * S3 必填校验
     */
    protected function assertS3Fields(string $storage, array $data, bool $requireSecret): void
    {
        if ($storage !== 's3') {
            return;
        }
        foreach (['endpoint', 'bucket', 'access_key'] as $field) {
            if (trim((string) ($data[$field] ?? '')) === '') {
                throw new \InvalidArgumentException('S3 需填写节点、Bucket、AccessKey');
            }
        }
        if ($requireSecret && trim((string) ($data['access_secret'] ?? '')) === '') {
            throw new \InvalidArgumentException('S3 需填写 AccessSecret');
        }
        if (trim((string) ($data['domain'] ?? '')) === '') {
            throw new \InvalidArgumentException('S3 需填写自定义域名');
        }
    }
}
