<?php
declare(strict_types=1);

namespace app\service;

use app\common\addon\AddonManager;
use app\common\PageHelper;
use think\facade\Db;

/**
 * 插件管理
 */
class AddonService
{
    protected AddonManager $manager;

    public function __construct()
    {
        $this->manager = AddonManager::instance();
    }

    /**
     * 列表（扫描目录 + 安装状态）
     * @return array{0: array, 1: int}
     */
    public function selectList(array $params = []): array
    {
        $rows = [];
        $dbMap = [];
        try {
            foreach (Db::name('sys_addon')->select()->toArray() as $row) {
                $dbMap[$row['name']] = $row;
            }
        } catch (\Throwable) {
            $dbMap = [];
        }

        $keyword = trim((string) ($params['name'] ?? $params['title'] ?? ''));
        $status = $params['status'] ?? '';

        foreach ($this->manager->scan() as $info) {
            $name = $info['name'];
            $db = $dbMap[$name] ?? null;
            $installed = $db !== null;
            $rowStatus = $installed ? (string) ($db['status'] ?? '0') : '-1';
            if ($status !== '' && $rowStatus !== (string) $status) {
                continue;
            }
            if ($keyword !== '' && stripos($name . ($info['title'] ?? ''), $keyword) === false) {
                continue;
            }
            $rows[] = [
                'name'        => $name,
                'title'       => $info['title'] ?? $name,
                'version'     => $db['version'] ?? ($info['version'] ?? ''),
                'description' => $info['description'] ?? ($info['intro'] ?? ''),
                'author'      => $info['author'] ?? '',
                'status'      => $rowStatus,
                'installed'   => $installed,
                'installTime' => $db['install_time'] ?? '',
                'hasConfig'   => is_file(addon_path($name) . 'config.php'),
            ];
        }

        $pageNum = max(1, (int) ($params['pageNum'] ?? $params['page_num'] ?? 1));
        $pageSize = max(1, (int) ($params['pageSize'] ?? $params['page_size'] ?? 10));
        $total = count($rows);
        $slice = array_slice($rows, ($pageNum - 1) * $pageSize, $pageSize);
        return [PageHelper::camelRows($slice), $total];
    }

    /**
     * 安装
     */
    public function install(string $name): int
    {
        $this->manager->install($name);
        return 1;
    }

    /**
     * 卸载
     */
    public function uninstall(string $name): int
    {
        $this->manager->uninstall($name);
        return 1;
    }

    /**
     * 启用
     */
    public function enable(string $name): int
    {
        $this->manager->enable($name);
        return 1;
    }

    /**
     * 禁用
     */
    public function disable(string $name): int
    {
        $this->manager->disable($name);
        return 1;
    }

    /**
     * 上传 zip：未安装则安装，已安装且版本更新则覆盖升级（单次解压）
     * @return array{name:string,action:string,from:string,to:string}
     */
    public function uploadZip(string $tmpFile, bool $install = true): array
    {
        $meta = $this->manager->peekZipInfo($tmpFile);
        if ($this->manager->isInstalled($meta['name'])) {
            return $this->manager->upgradeFromPeek($meta);
        }
        $name = $this->manager->commitPeek($meta);
        if ($install) {
            $this->manager->install($name);
        }
        $info = $this->manager->getInfo($name);
        return [
            'name'   => $name,
            'action' => 'install',
            'from'   => '',
            'to'     => (string) ($info['version'] ?? ($meta['version'] ?? '')),
        ];
    }

    /**
     * 配置页数据
     */
    public function getConfigForm(string $name): array
    {
        $schema = $this->manager->getConfigSchema($name);
        $values = $this->manager->getConfig($name);
        $schema = $this->manager->fillSchema($schema, $values);
        foreach ($schema as &$item) {
            if (!is_array($item)) {
                continue;
            }
            if (isset($item['type']) && $item['type'] === 'array' && is_array($item['value'] ?? null)) {
                $item['valueText'] = json_encode($item['value'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } else {
                $item['valueText'] = is_array($item['value'] ?? null)
                    ? json_encode($item['value'], JSON_UNESCAPED_UNICODE)
                    : (string) ($item['value'] ?? '');
            }
        }
        unset($item);
        return [
            'name'   => $name,
            'info'   => $this->manager->getInfo($name),
            'schema' => $schema,
            'values' => $values,
        ];
    }

    /**
     * 保存配置
     */
    public function saveConfig(string $name, array $post): int
    {
        $schema = $this->manager->getConfigSchema($name);
        $current = $this->manager->getConfig($name);
        $incoming = $post['config'] ?? $post;
        if (!is_array($incoming)) {
            $incoming = [];
        }
        foreach ($incoming as $k => $v) {
            if (in_array($k, ['name', 'addonName'], true)) {
                continue;
            }
            if (is_string($v) && (str_starts_with(trim($v), '{') || str_starts_with(trim($v), '['))) {
                $decoded = json_decode($v, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $v = $decoded;
                }
            }
            $current[$k] = $v;
        }
        $flatDefaults = $this->manager->schemaToFlat($schema);
        $current = array_merge($flatDefaults, $current);
        $this->manager->setConfig($name, $current);
        return 1;
    }
}
