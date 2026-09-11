<?php
declare(strict_types=1);

namespace app\common\addon;

use think\facade\Db;
use think\facade\Event;
use ZipArchive;

/**
 * 插件运行时管理
 */
class AddonManager
{
    private static ?self $instance = null;

    /** @var array<string, array> info 缓存 */
    private array $infoCache = [];

    /** @var array<string, array> 配置缓存 */
    private array $configCache = [];

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * 扫描 addons 目录
     * @return array<int, array>
     */
    public function scan(): array
    {
        $list = [];
        if (!is_dir(addon_path())) {
            return $list;
        }
        foreach (scandir(addon_path()) ?: [] as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            $infoFile = addon_path($dir) . 'info.ini';
            if (!is_file($infoFile)) {
                continue;
            }
            $info = $this->getInfo($dir);
            if ($info) {
                $list[] = $info;
            }
        }
        usort($list, static fn ($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));
        return $list;
    }

    /**
     * 读取 info.ini
     */
    public function getInfo(string $name): array
    {
        if (isset($this->infoCache[$name])) {
            return $this->infoCache[$name];
        }
        $file = addon_path($name) . 'info.ini';
        if (!is_file($file)) {
            return [];
        }
        $info = parse_ini_file($file, false, INI_SCANNER_RAW) ?: [];
        $info['name'] = $info['name'] ?? $name;
        $info['title'] = $info['title'] ?? $name;
        $info['version'] = $info['version'] ?? '1.0.0';
        $info['state'] = (string) ($info['state'] ?? '0');
        $this->infoCache[$name] = $info;
        return $info;
    }

    /**
     * 写入 info.ini
     */
    public function setInfo(string $name, array $info): bool
    {
        $file = addon_path($name) . 'info.ini';
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $lines = [];
        foreach ($info as $k => $v) {
            if (is_array($v)) {
                continue;
            }
            $lines[] = $k . ' = ' . $v;
        }
        $ok = file_put_contents($file, implode("\n", $lines) . "\n") !== false;
        unset($this->infoCache[$name]);
        return $ok;
    }

    /**
     * 配置 schema（config.php 原始结构）
     */
    public function getConfigSchema(string $name): array
    {
        $file = addon_path($name) . 'config.php';
        if (!is_file($file)) {
            return [];
        }
        $schema = include $file;
        return is_array($schema) ? $schema : [];
    }

    /**
     * 扁平配置 name => value
     */
    public function getConfig(string $name): array
    {
        if (isset($this->configCache[$name])) {
            return $this->configCache[$name];
        }
        $dataFile = addon_path($name) . 'data' . DIRECTORY_SEPARATOR . 'config.php';
        if (is_file($dataFile)) {
            $values = include $dataFile;
            if (is_array($values)) {
                $this->configCache[$name] = $values;
                return $values;
            }
        }
        $flat = $this->schemaToFlat($this->getConfigSchema($name));
        $this->configCache[$name] = $flat;
        return $flat;
    }

    /**
     * 保存扁平配置
     */
    public function setConfig(string $name, array $config): bool
    {
        $dir = addon_path($name) . 'data';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $content = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export($config, true) . ";\n";
        $ok = file_put_contents($dir . DIRECTORY_SEPARATOR . 'config.php', $content) !== false;
        unset($this->configCache[$name]);
        if ($ok) {
            $this->syncDbRow($name, ['config' => json_encode($config, JSON_UNESCAPED_UNICODE)]);
        }
        return $ok;
    }

    /**
     * schema 转扁平
     */
    public function schemaToFlat(array $schema): array
    {
        $flat = [];
        if ($schema === []) {
            return $flat;
        }
        // 已是 name=>value
        if (array_is_list($schema) === false && !isset($schema[0]['name'])) {
            return $schema;
        }
        foreach ($schema as $item) {
            if (!is_array($item) || !isset($item['name'])) {
                continue;
            }
            $flat[$item['name']] = $item['value'] ?? '';
        }
        return $flat;
    }

    /**
     * 用扁平值回填 schema
     */
    public function fillSchema(array $schema, array $values): array
    {
        if ($schema === [] || (array_is_list($schema) === false && !isset($schema[0]['name']))) {
            return $schema;
        }
        foreach ($schema as &$item) {
            if (!is_array($item) || !isset($item['name'])) {
                continue;
            }
            if (array_key_exists($item['name'], $values)) {
                $item['value'] = $values[$item['name']];
            }
        }
        unset($item);
        return $schema;
    }

    /**
     * 是否已安装（sys_addon 有记录）
     */
    public function isInstalled(string $name): bool
    {
        try {
            return Db::name('sys_addon')->where('name', $name)->count() > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * 已安装版本（优先 sys_addon.version，缺省读磁盘 info.ini）
     */
    public function getInstalledVersion(string $name): string
    {
        try {
            $row = Db::name('sys_addon')->where('name', $name)->find();
            if ($row && trim((string) ($row['version'] ?? '')) !== '') {
                return (string) $row['version'];
            }
        } catch (\Throwable) {
        }
        $info = $this->getInfo($name);
        return (string) ($info['version'] ?? '0.0.0');
    }

    /**
     * 是否启用
     */
    public function isEnabled(string $name): bool
    {
        // 以 sys_addon.status 为准（路由合并依赖此判断）
        try {
            $row = Db::name('sys_addon')->where('name', $name)->find();
            if ($row) {
                return (string) ($row['status'] ?? '0') === '1';
            }
        } catch (\Throwable) {
        }
        $info = $this->getInfo($name);
        return ($info['state'] ?? '0') === '1';
    }

    /**
     * 已启用插件名列表
     * @return string[]
     */
    public function enabledNames(): array
    {
        $names = [];
        foreach ($this->scan() as $info) {
            $name = $info['name'] ?? '';
            if ($name !== '' && $this->isEnabled($name)) {
                $names[] = $name;
            }
        }
        return $names;
    }

    /**
     * 实例化插件主类
     */
    public function getInstance(string $name): ?Addon
    {
        $class = $this->getAddonClass($name);
        if (!$class || !class_exists($class)) {
            return null;
        }
        $obj = new $class($name);
        return $obj instanceof Addon ? $obj : null;
    }

    /**
     * 主类全名
     */
    public function getAddonClass(string $name): string
    {
        $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
        return 'addons\\' . $name . '\\' . $className;
    }

    /**
     * 安装插件
     */
    public function install(string $name): bool
    {
        if ($this->isInstalled($name)) {
            throw new \RuntimeException('插件已安装');
        }
        $info = $this->getInfo($name);
        if (!$info) {
            throw new \RuntimeException('插件不存在');
        }

        $this->runInstallSql($name);
        $this->syncMenus($name, true);
        // 先隐藏菜单，避免未启用时点击
        $this->setMenuVisible($name, false);

        $addon = $this->getInstance($name);
        if ($addon && !$addon->install()) {
            throw new \RuntimeException('插件 install() 返回失败');
        }

        $now = date('Y-m-d H:i:s');
        Db::name('sys_addon')->insert([
            'name'         => $name,
            'title'        => $info['title'] ?? $name,
            'version'      => $info['version'] ?? '1.0.0',
            'description'  => $info['description'] ?? ($info['intro'] ?? ''),
            'author'       => $info['author'] ?? '',
            'status'       => '0',
            'config'       => json_encode($this->getConfig($name), JSON_UNESCAPED_UNICODE),
            'install_time' => $now,
            'create_time'  => $now,
            'update_time'  => $now,
            'remark'       => '',
        ]);

        // 安装后自动启用，合并路由
        $this->enable($name);

        return true;
    }

    /**
     * 卸载插件
     */
    public function uninstall(string $name): bool
    {
        if (!$this->isInstalled($name)) {
            throw new \RuntimeException('插件未安装');
        }
        if ($this->isEnabled($name)) {
            throw new \RuntimeException('请先禁用插件再卸载');
        }

        $addon = $this->getInstance($name);
        if ($addon && !$addon->uninstall()) {
            throw new \RuntimeException('插件 uninstall() 返回失败');
        }

        $this->removeMenus($name);
        Db::name('sys_addon')->where('name', $name)->delete();

        $info = $this->getInfo($name);
        if ($info) {
            $info['state'] = '0';
            $this->setInfo($name, $info);
        }
        return true;
    }

    /**
     * 启用
     */
    public function enable(string $name): bool
    {
        if (!$this->isInstalled($name)) {
            throw new \RuntimeException('插件未安装');
        }
        $addon = $this->getInstance($name);
        if ($addon && !$addon->enable()) {
            throw new \RuntimeException('插件 enable() 返回失败');
        }
        $this->setMenuVisible($name, true);
        $info = $this->getInfo($name);
        $info['state'] = '1';
        $this->setInfo($name, $info);
        $this->syncDbRow($name, ['status' => '1', 'update_time' => date('Y-m-d H:i:s')]);
        return true;
    }

    /**
     * 禁用
     */
    public function disable(string $name): bool
    {
        if (!$this->isInstalled($name)) {
            throw new \RuntimeException('插件未安装');
        }
        $addon = $this->getInstance($name);
        if ($addon && !$addon->disable()) {
            throw new \RuntimeException('插件 disable() 返回失败');
        }
        $this->setMenuVisible($name, false);
        $info = $this->getInfo($name);
        $info['state'] = '0';
        $this->setInfo($name, $info);
        $this->syncDbRow($name, ['status' => '0', 'update_time' => date('Y-m-d H:i:s')]);
        return true;
    }

    /**
     * 解压 zip 到临时目录并解析 info.ini（调用方负责清理或接管）
     * @return array{name:string,version:string,title:string,description:string,author:string,tmpRoot:string,addonRoot:string}
     */
    public function peekZipInfo(string $zipPath): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new \RuntimeException('未启用 ZipArchive 扩展');
        }
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('无法打开 zip');
        }
        $tmp = runtime_path() . 'addons' . DIRECTORY_SEPARATOR . 'tmp_' . uniqid();
        if (!is_dir(dirname($tmp))) {
            mkdir(dirname($tmp), 0755, true);
        }
        mkdir($tmp, 0755, true);
        $zip->extractTo($tmp);
        $zip->close();

        $infoFile = $this->findInfoIni($tmp);
        if (!$infoFile) {
            $this->rmDir($tmp);
            throw new \RuntimeException('zip 中缺少 info.ini');
        }
        $addonRoot = dirname($infoFile);
        $info = parse_ini_file($infoFile, false, INI_SCANNER_RAW) ?: [];
        $name = (string) ($info['name'] ?? basename($addonRoot));
        if (!preg_match('/^[a-z][a-z0-9_-]*$/i', $name)) {
            $this->rmDir($tmp);
            throw new \RuntimeException('插件标识不合法');
        }
        return [
            'name'        => $name,
            'version'     => (string) ($info['version'] ?? '1.0.0'),
            'title'       => (string) ($info['title'] ?? $name),
            'description' => (string) ($info['description'] ?? ($info['intro'] ?? '')),
            'author'      => (string) ($info['author'] ?? ''),
            'tmpRoot'     => $tmp,
            'addonRoot'   => $addonRoot,
        ];
    }

    /**
     * 清理 peekZipInfo 产生的临时目录
     */
    public function discardPeek(array $meta): void
    {
        $tmp = (string) ($meta['tmpRoot'] ?? '');
        if ($tmp !== '') {
            $this->rmDir($tmp);
        }
    }

    /**
     * 将 peek 结果迁入 addons/（禁止覆盖已有目录）
     */
    public function commitPeek(array $meta): string
    {
        $name = (string) ($meta['name'] ?? '');
        $tmpRoot = (string) ($meta['tmpRoot'] ?? '');
        $addonRoot = (string) ($meta['addonRoot'] ?? '');
        try {
            if ($name === '' || $addonRoot === '' || !is_dir($addonRoot)) {
                throw new \RuntimeException('插件包无效');
            }
            $target = rtrim(addon_path($name), '/\\');
            if (is_dir($target)) {
                throw new \RuntimeException("插件目录已存在: {$name}");
            }
            if (!@rename($addonRoot, $target)) {
                throw new \RuntimeException('无法写入插件目录');
            }
            unset($this->infoCache[$name], $this->configCache[$name]);
            return $name;
        } finally {
            if ($tmpRoot !== '') {
                $this->rmDir($tmpRoot);
            }
        }
    }

    /**
     * 本地 zip 安装到 addons/（目录已存在则拒绝）
     */
    public function extractZip(string $zipPath): string
    {
        return $this->commitPeek($this->peekZipInfo($zipPath));
    }

    /**
     * 用 zip 覆盖升级已安装插件（不重跑 install.sql / Addon::install）
     * @return array{name:string,action:string,from:string,to:string}
     */
    public function upgradeFromZip(string $zipPath): array
    {
        return $this->upgradeFromPeek($this->peekZipInfo($zipPath));
    }

    /**
     * 基于已 peek 的临时目录执行覆盖升级
     * @param array{name:string,version:string,tmpRoot:string,addonRoot:string} $meta
     * @return array{name:string,action:string,from:string,to:string}
     */
    public function upgradeFromPeek(array $meta): array
    {
        $name = (string) ($meta['name'] ?? '');
        $newVersion = (string) ($meta['version'] ?? '');
        $tmpRoot = (string) ($meta['tmpRoot'] ?? '');
        $addonRoot = (string) ($meta['addonRoot'] ?? '');

        try {
            if ($name === '' || $addonRoot === '' || !is_dir($addonRoot)) {
                throw new \RuntimeException('插件包无效');
            }
            if (!$this->isInstalled($name)) {
                throw new \RuntimeException('插件未安装，无法升级');
            }
            $oldVersion = $this->getInstalledVersion($name);
            if ($newVersion === '' || version_compare($newVersion, $oldVersion, '<=') ) {
                throw new \RuntimeException("上传版本 {$newVersion} 必须大于已安装版本 {$oldVersion}");
            }

            $wasEnabled = $this->isEnabled($name);
            if ($wasEnabled) {
                $this->disable($name);
            }

            // 保留库内配置，覆盖目录后再写回磁盘
            $configBackup = null;
            try {
                $row = Db::name('sys_addon')->where('name', $name)->find();
                if ($row && isset($row['config']) && $row['config'] !== '' && $row['config'] !== null) {
                    $decoded = json_decode((string) $row['config'], true);
                    if (is_array($decoded)) {
                        $configBackup = $decoded;
                    }
                }
            } catch (\Throwable) {
            }

            $target = rtrim(addon_path($name), '/\\');
            if (is_dir($target)) {
                $this->rmDir($target);
            }
            if (!@rename($addonRoot, $target)) {
                throw new \RuntimeException('无法写入插件目录');
            }

            unset($this->infoCache[$name], $this->configCache[$name]);

            if ($configBackup !== null) {
                $this->setConfig($name, $configBackup);
            }

            $this->syncMenus($name, true);

            $info = $this->getInfo($name);
            $toVersion = (string) ($info['version'] ?? $newVersion);
            $this->syncDbRow($name, [
                'version'     => $toVersion,
                'title'       => (string) ($info['title'] ?? $name),
                'description' => (string) ($info['description'] ?? ($info['intro'] ?? '')),
                'author'      => (string) ($info['author'] ?? ''),
                'update_time' => date('Y-m-d H:i:s'),
            ]);

            if ($wasEnabled) {
                $this->enable($name);
            } else {
                $this->setMenuVisible($name, false);
            }

            return [
                'name'   => $name,
                'action' => 'upgrade',
                'from'   => $oldVersion,
                'to'     => $toVersion,
            ];
        } finally {
            if ($tmpRoot !== '') {
                $this->rmDir($tmpRoot);
            }
        }
    }
    /**
     * 合并公开路由
     */
    public function mergePublicRoutes(): void
    {
        foreach ($this->enabledNames() as $name) {
            $this->loadRouteFile($name, 'public');
        }
        // 静态资源兜底
        \think\facade\Route::get('addons/:name/[:path]', '\app\controller\AddonAsset@index')
            ->pattern(['path' => '[\w\-\.\/]+']);
    }

    /**
     * 合并需登录路由
     */
    public function mergeAuthRoutes(): void
    {
        foreach ($this->enabledNames() as $name) {
            $this->loadRouteFile($name, 'auth');
        }
    }

    /**
     * 注册已启用插件事件方法
     */
    public function registerEvents(): void
    {
        foreach ($this->enabledNames() as $name) {
            $addon = $this->getInstance($name);
            if (!$addon) {
                continue;
            }
            $ref = new \ReflectionClass($addon);
            foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $mName = $method->getName();
                if (in_array($mName, ['install', 'uninstall', 'enable', 'disable', 'getName', 'getInfo', 'getConfig', '__construct'], true)) {
                    continue;
                }
                if (str_starts_with($mName, '_')) {
                    continue;
                }
                Event::listen($mName, [$addon, $mName]);
            }
        }
    }

    /**
     * 触发事件
     */
    public function hook(string $event, mixed $params = null): mixed
    {
        return Event::trigger($event, $params);
    }

    /**
     * 启动时注册事件
     */
    public function boot(): void
    {
        $base = addon_path();
        if (!is_dir($base)) {
            @mkdir($base, 0755, true);
        }
        try {
            $this->registerEvents();
        } catch (\Throwable) {
            // 表未创建时忽略
        }
    }

    /**
     * 执行 install.sql
     */
    public function runInstallSql(string $name): void
    {
        $file = addon_path($name) . 'install.sql';
        if (!is_file($file)) {
            return;
        }
        $sql = file_get_contents($file);
        if ($sql === false || trim($sql) === '') {
            return;
        }
        $sql = str_replace(["\r\n", "\r"], "\n", $sql);
        $parts = array_filter(array_map('trim', explode(";\n", $sql)));
        foreach ($parts as $statement) {
            // 去掉语句前的行注释，避免 "-- xxx\nCREATE ..." 被整段跳过
            $lines = preg_split("/\n/", $statement) ?: [];
            $kept = [];
            foreach ($lines as $line) {
                $trimLine = ltrim($line);
                if ($trimLine === '' || str_starts_with($trimLine, '--')) {
                    continue;
                }
                $kept[] = $line;
            }
            $statement = trim(implode("\n", $kept));
            if ($statement === '') {
                continue;
            }
            Db::execute($statement);
        }
    }

    /**
     * 同步 menu.php 到 sys_menu
     */
    public function syncMenus(string $name, bool $force = false): void
    {
        $file = addon_path($name) . 'menu.php';
        if (!is_file($file)) {
            return;
        }
        $menus = include $file;
        if (!is_array($menus) || $menus === []) {
            return;
        }
        if ($force) {
            $this->removeMenus($name);
        }
        $this->insertMenuTree($menus, 0, $name);
    }

    /**
     * 按 remark=addon:{name} 删除菜单
     */
    public function removeMenus(string $name): void
    {
        $mark = 'addon:' . $name;
        $ids = Db::name('sys_menu')->where('remark', $mark)->column('menu_id');
        if (!$ids) {
            return;
        }
        // 含子节点（同样 remark）一并删
        Db::name('sys_role_menu')->whereIn('menu_id', $ids)->delete();
        Db::name('sys_menu')->whereIn('menu_id', $ids)->delete();
    }

    /**
     * 启停时显示/隐藏插件菜单
     */
    public function setMenuVisible(string $name, bool $visible): void
    {
        $mark = 'addon:' . $name;
        Db::name('sys_menu')->where('remark', $mark)->update([
            'visible'     => $visible ? '0' : '1',
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 递归写入菜单树
     */
    private function insertMenuTree(array $menus, int $parentId, string $addonName): void
    {
        $mark = 'addon:' . $addonName;
        foreach ($menus as $menu) {
            if (!is_array($menu)) {
                continue;
            }
            $children = $menu['children'] ?? $menu['child'] ?? [];
            unset($menu['children'], $menu['child']);
            $row = [
                'menu_name'   => $menu['menu_name'] ?? ($menu['title'] ?? ''),
                // 递归写入时强制挂到父节点；仅根层可用 menu 里的 parent_id（如 0 顶级）
                'parent_id'   => $parentId > 0 ? $parentId : (int) ($menu['parent_id'] ?? 0),
                'order_num'   => (int) ($menu['order_num'] ?? $menu['weigh'] ?? 0),
                'url'         => $menu['url'] ?? '#',
                'target'      => $menu['target'] ?? '',
                'menu_type'   => $menu['menu_type'] ?? 'C',
                'visible'     => (string) ($menu['visible'] ?? '0'),
                'is_refresh'  => (string) ($menu['is_refresh'] ?? '1'),
                'perms'       => $menu['perms'] ?? '',
                'icon'        => $menu['icon'] ?? '#',
                'create_by'   => 'admin',
                'create_time' => date('Y-m-d H:i:s'),
                'remark'      => $mark,
            ];
            $id = (int) Db::name('sys_menu')->insertGetId($row);
            if (is_array($children) && $children !== []) {
                $this->insertMenuTree($children, $id, $addonName);
            }
        }
    }

    private function loadRouteFile(string $name, string $group): void
    {
        $file = addon_path($name) . 'route.php';
        if (!is_file($file)) {
            return;
        }
        $routes = include $file;
        if (!is_array($routes)) {
            return;
        }
        $callback = $routes[$group] ?? null;
        if ($callback instanceof \Closure) {
            $callback();
        }
    }

    private function syncDbRow(string $name, array $data): void
    {
        try {
            if (Db::name('sys_addon')->where('name', $name)->count() > 0) {
                Db::name('sys_addon')->where('name', $name)->update($data);
            }
        } catch (\Throwable) {
        }
    }

    private function findInfoIni(string $dir): ?string
    {
        $direct = $dir . DIRECTORY_SEPARATOR . 'info.ini';
        if (is_file($direct)) {
            return $direct;
        }
        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $sub = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($sub) && is_file($sub . DIRECTORY_SEPARATOR . 'info.ini')) {
                return $sub . DIRECTORY_SEPARATOR . 'info.ini';
            }
        }
        return null;
    }

    private function rmDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->rmDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
