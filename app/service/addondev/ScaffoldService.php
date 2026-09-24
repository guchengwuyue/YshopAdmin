<?php
declare(strict_types=1);

namespace app\service\addondev;

use app\common\addon\AddonManager;
use app\common\PageHelper;

/**
 * 插件脚手架（CMS 向：Vue 后台页 + site 主题桩，无 Think view HTML）
 */
class ScaffoldService
{
    /** @var string[] 系统保留标识 */
    protected array $reserved = [
        'system', 'monitor', 'tool', 'index', 'login',
        'pay', 'wechat', 'file', 'addondev',
    ];

    /**
     * 分页列表
     * @return array{0: array, 1: int}
     */
    public function selectList(array $params = []): array
    {
        $rows = [];
        $keyword = trim((string) ($params['name'] ?? $params['title'] ?? ''));
        foreach (AddonManager::instance()->scan() as $info) {
            if ($keyword !== '' && stripos(($info['name'] ?? '') . ($info['title'] ?? ''), $keyword) === false) {
                continue;
            }
            $rows[] = [
                'name'    => $info['name'] ?? '',
                'title'   => $info['title'] ?? '',
                'version' => $info['version'] ?? '',
                'author'  => $info['author'] ?? '',
                'intro'   => $info['intro'] ?? '',
                'state'   => (string) ($info['state'] ?? '0'),
            ];
        }
        $pageNum = max(1, (int) ($params['pageNum'] ?? $params['page_num'] ?? 1));
        $pageSize = max(1, (int) ($params['pageSize'] ?? $params['page_size'] ?? 10));
        $total = count($rows);
        $slice = array_slice($rows, ($pageNum - 1) * $pageSize, $pageSize);
        return [PageHelper::camelRows($slice), $total];
    }

    /**
     * 创建插件骨架
     */
    public function create(array $data): int
    {
        $name = strtolower(trim((string) ($data['name'] ?? '')));
        if ($name === '' || !preg_match('/^[a-z][a-z0-9]*$/', $name)) {
            throw new \InvalidArgumentException('插件标识须以小写字母开头，仅允许小写字母与数字');
        }
        if (in_array($name, $this->reserved, true)) {
            throw new \InvalidArgumentException('插件标识为系统保留名');
        }
        $dir = addon_path($name);
        if (is_dir($dir)) {
            throw new \RuntimeException('插件已存在');
        }
        $title = trim((string) ($data['title'] ?? $name));
        $version = trim((string) ($data['version'] ?? '1.0.0'));
        $author = trim((string) ($data['author'] ?? 'YshopAdmin'));
        $intro = trim((string) ($data['intro'] ?? ''));
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            throw new \InvalidArgumentException('版本号格式为 x.y.z');
        }
        $Name = ucfirst($name);
        $vars = [
            'name'    => $name,
            'Name'    => $Name,
            'title'   => $title,
            'author'  => $author,
            'version' => $version,
            'intro'   => $intro,
            'website' => trim((string) ($data['website'] ?? '')),
            'state'   => '0',
            'url'     => '/' . $name . '/index',
        ];
        $map = [
            'info.ini'            => '',
            'AddonMain.tpl'       => $Name . '.php',
            'config.tpl'          => 'config.php',
            'menu.tpl'            => 'menu.php',
            'route.tpl'           => 'route.php',
            'IndexController.tpl' => 'controller' . DIRECTORY_SEPARATOR . 'Index.php',
            'IndexService.tpl'    => 'service' . DIRECTORY_SEPARATOR . 'IndexService.php',
            'admin.index.vue.tpl' => 'web' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'index' . DIRECTORY_SEPARATOR . 'index.vue',
            'theme.json.tpl'      => 'web' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'theme.json',
            'variables.scss.tpl'  => 'web' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'styles' . DIRECTORY_SEPARATOR . '_variables.scss',
        ];
        foreach ([
            'controller',
            'service',
            'library',
            'public',
            'web/admin/index',
            'web/site/themes/default/styles',
        ] as $sub) {
            $path = $dir . str_replace('/', DIRECTORY_SEPARATOR, $sub);
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
        foreach ($map as $tpl => $as) {
            $this->writeTpl($tpl, $vars, $dir . ($as !== '' ? $as : $tpl));
        }
        file_put_contents($dir . 'install.sql', "");
        file_put_contents($dir . 'uninstall.sql', "");
        return 1;
    }

    /**
     * 更新 info.ini
     */
    public function update(string $name, array $data): int
    {
        $info = get_addon_info($name);
        if (!$info) {
            throw new \RuntimeException('插件不存在');
        }
        $postName = strtolower(trim((string) ($data['name'] ?? $name)));
        if ($postName !== $name) {
            throw new \InvalidArgumentException('插件标识不能修改');
        }
        $version = trim((string) ($data['version'] ?? $info['version'] ?? '1.0.0'));
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            throw new \InvalidArgumentException('版本号格式为 x.y.z');
        }
        $info['title'] = trim((string) ($data['title'] ?? $info['title'] ?? $name));
        $info['author'] = trim((string) ($data['author'] ?? $info['author'] ?? ''));
        $info['intro'] = trim((string) ($data['intro'] ?? $info['intro'] ?? ''));
        $info['version'] = $version;
        $info['website'] = trim((string) ($data['website'] ?? $info['website'] ?? ''));
        $info['name'] = $name;
        if (!set_addon_info($name, $info)) {
            return 0;
        }
        return 1;
    }

    /**
     * 写入模板文件
     */
    protected function writeTpl(string $tplName, array $vars, string $target): void
    {
        $tpl = root_path() . 'app' . DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR
            . 'addondev' . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR . $tplName;
        if (!is_file($tpl)) {
            throw new \RuntimeException('template missing: ' . $tplName);
        }
        $content = file_get_contents($tpl);
        $search = $replace = [];
        foreach ($vars as $k => $v) {
            $search[] = '{$' . $k . '}';
            $replace[] = (string) $v;
        }
        $content = str_replace($search, $replace, $content);
        $dir = dirname($target);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($target, $content);
    }
}
