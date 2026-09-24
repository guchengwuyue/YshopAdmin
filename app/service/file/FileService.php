<?php
declare(strict_types=1);

namespace app\service\file;

use app\library\file\StorageFactory;
use app\common\Auth;
use app\common\PageHelper;
use think\facade\Db;
use think\file\UploadedFile;

/**
 * 文件元数据与上传
 */
class FileService
{
    /**
     * 查询文件列表
     */
    public function selectList(array $params = []): array
    {
        PageHelper::startPage();
        $query = Db::name('ys_file')->where('del_flag', '0');
        $path = (string) ($params['path'] ?? '');
        if ($path !== '') {
            $query->whereLike('path', '%' . $path . '%');
        }
        $name = (string) ($params['name'] ?? $params['keyword'] ?? '');
        if ($name !== '') {
            $query->whereLike('name', '%' . $name . '%');
        }
        $type = (string) ($params['type'] ?? '');
        if ($type !== '') {
            $query->whereLike('type', '%' . $type . '%');
        }
        $mimetype = (string) ($params['mimetype'] ?? '');
        if ($mimetype !== '') {
            if (str_ends_with($mimetype, '/*')) {
                $prefix = substr($mimetype, 0, -1);
                $query->whereLike('type', $prefix . '%');
            } else {
                $query->where('type', $mimetype);
            }
        }
        $beginTime = $params['beginTime'] ?? ($params['params']['beginTime'] ?? '');
        $endTime = $params['endTime'] ?? ($params['params']['endTime'] ?? '');
        if ($beginTime !== '') {
            $query->where('create_time', '>=', $beginTime);
        }
        if ($endTime !== '') {
            $query->where('create_time', '<=', $endTime . ' 23:59:59');
        }
        if (array_key_exists('groupId', $params) || array_key_exists('group_id', $params)) {
            $groupId = $params['groupId'] ?? $params['group_id'];
            if ($groupId !== '' && $groupId !== null) {
                $query->where('group_id', (int) $groupId);
            }
        }
        $query->order('id', 'desc');
        [$rows, $total] = PageHelper::paginate($query);
        $rows = PageHelper::camelRows($rows);
        $domainMap = $this->loadConfigDomains($rows);
        foreach ($rows as &$row) {
            $row['sizeText'] = $this->formatSize((int) ($row['size'] ?? 0));
            $row['isImage'] = $this->isImageMime((string) ($row['type'] ?? ''));
            $cfgId = (int) ($row['configId'] ?? 0);
            $row['url'] = $this->resolvePublicUrl((string) ($row['url'] ?? ''), $domainMap[$cfgId] ?? '');
        }
        unset($row);
        return [$rows, $total];
    }

    /**
     * 批量取配置域名
     * @param array<int,array> $rows
     * @return array<int,string>
     */
    protected function loadConfigDomains(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['configId'] ?? 0);
            if ($id > 0) {
                $ids[$id] = true;
            }
        }
        if ($ids === []) {
            return [];
        }
        $list = Db::name('ys_file_config')->whereIn('id', array_keys($ids))->where('del_flag', '0')
            ->column('domain', 'id');
        $map = [];
        foreach ($list as $id => $domain) {
            $map[(int) $id] = trim((string) $domain);
        }
        return $map;
    }

    /**
     * 相对地址补全配置域名
     */
    public function resolvePublicUrl(string $url, string $domain): string
    {
        $url = trim($url);
        if ($url === '' || preg_match('#^(https?:)?//#i', $url) || str_starts_with($url, 'data:')) {
            return $url;
        }
        $domain = rtrim(trim($domain), '/');
        if ($domain === '') {
            return str_starts_with($url, '/') ? $url : '/' . ltrim($url, '/');
        }
        // 域名仅主机时：http://host + /storage/...
        if (preg_match('#^https?://[^/]+$#i', $domain) && str_starts_with($url, '/')) {
            return $domain . $url;
        }
        $parts = parse_url($domain);
        if (is_array($parts) && isset($parts['scheme'], $parts['host']) && str_starts_with($url, '/')) {
            $base = $parts['scheme'] . '://' . $parts['host']
                . (isset($parts['port']) ? ':' . $parts['port'] : '');
            $domainPath = rtrim((string) ($parts['path'] ?? ''), '/');
            if ($domainPath !== '' && (str_starts_with($url, $domainPath . '/') || $url === $domainPath)) {
                return $base . $url;
            }
        }
        return $domain . '/' . ltrim($url, '/');
    }

    /**
     * 使用主配置上传
     * @return array{id:int,name:string,path:string,url:string,size:int,type:string,groupId:int}
     */
    public function uploadByMaster(UploadedFile|string $file, string $dir = '', int $groupId = 0): array
    {
        $cfg = (new ConfigService())->getMasterRaw();
        if (!$cfg) {
            throw new \RuntimeException('请先配置并设置主存储配置');
        }
        return $this->upload($file, $cfg, $dir, $groupId);
    }

    /**
     * 上传并写元数据
     * @return array{id:int,name:string,path:string,url:string,size:int,type:string,groupId:int}
     */
    public function upload(UploadedFile|string $file, array $config, string $dir = '', int $groupId = 0): array
    {
        if ($file instanceof UploadedFile) {
            $origName = $file->getOriginalName();
            $mime = (string) ($file->getMime() ?: 'application/octet-stream');
            $size = (int) $file->getSize();
            $contents = file_get_contents($file->getPathname());
            if ($contents === false) {
                throw new \RuntimeException('读取上传文件失败');
            }
        } else {
            if (!is_file($file)) {
                throw new \InvalidArgumentException('文件不存在');
            }
            $origName = basename($file);
            $mime = (string) (mime_content_type($file) ?: 'application/octet-stream');
            $size = (int) filesize($file);
            $contents = file_get_contents($file);
            if ($contents === false) {
                throw new \RuntimeException('读取文件失败');
            }
        }

        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $safeExt = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'bin';
        $dir = trim(str_replace('\\', '/', $dir), '/');
        $prefix = $dir !== '' ? $dir . '/' : '';
        $path = $prefix . date('Y/m/d') . '/' . date('His') . '_' . bin2hex(random_bytes(4)) . '.' . $safeExt;

        $driver = StorageFactory::make($config);
        $driver->put($path, $contents);
        $url = $driver->url($path);

        $groupId = max(0, $groupId);
        $id = (int) Db::name('ys_file')->insertGetId([
            'config_id'   => (int) ($config['id'] ?? 0),
            'group_id'    => $groupId,
            'name'        => $origName,
            'path'        => $path,
            'url'         => $url,
            'size'        => $size,
            'type'        => $mime,
            'create_time' => date('Y-m-d H:i:s'),
            'create_by'   => Auth::getLoginName(),
            'del_flag'    => '0',
        ]);

        return [
            'id'      => $id,
            'name'    => $origName,
            'path'    => $path,
            'url'     => $url,
            'size'    => $size,
            'type'    => $mime,
            'groupId' => $groupId,
        ];
    }

    /**
     * 批量移动到分组，groupId 为 0 表示移出分组
     */
    public function move(string $ids, int $groupId): int
    {
        $idArr = array_values(array_filter(array_map('intval', explode(',', $ids))));
        if (!$idArr) {
            return 0;
        }
        $groupId = max(0, $groupId);
        if ($groupId > 0) {
            $group = Db::name('ys_file_group')->where('id', $groupId)->where('del_flag', '0')->find();
            if (!$group) {
                throw new \InvalidArgumentException('目标分类不存在');
            }
        }
        Db::name('ys_file')->whereIn('id', $idArr)->where('del_flag', '0')->update([
            'group_id' => $groupId,
        ]);
        return (int) Db::name('ys_file')->whereIn('id', $idArr)->where('del_flag', '0')->count();
    }

    /**
     * 删除：物理对象 + 软删（物理失败仍软删并抛提示）
     */
    public function delete(string $ids): int
    {
        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        if (!$idArr) {
            return 0;
        }
        $rows = Db::name('ys_file')->whereIn('id', $idArr)->where('del_flag', '0')->select()->toArray();
        $warnings = [];
        foreach ($rows as $row) {
            $cfg = (new ConfigService())->getRaw((int) $row['config_id']);
            if ($cfg) {
                try {
                    StorageFactory::make($cfg)->delete((string) $row['path']);
                } catch (\Throwable $e) {
                    $warnings[] = $row['path'] . ': ' . $e->getMessage();
                }
            }
        }
        $n = Db::name('ys_file')->whereIn('id', $idArr)->where('del_flag', '0')->update([
            'del_flag' => '2',
        ]);
        if ($warnings !== []) {
            throw new \RuntimeException('记录已删除，物理删除部分失败: ' . implode('; ', $warnings));
        }
        return $n;
    }

    /**
     * 可读文件大小
     */
    public function formatSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 2) . ' KB';
        }
        if ($bytes < 1073741824) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        return round($bytes / 1073741824, 2) . ' GB';
    }

    /**
     * 是否图片 MIME
     */
    protected function isImageMime(string $mime): bool
    {
        return str_starts_with(strtolower($mime), 'image/');
    }
}
