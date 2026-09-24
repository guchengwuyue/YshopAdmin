<?php
declare(strict_types=1);

namespace app\service\addondev;

/**
 * 插件打包服务
 */
class PackageService
{
    /**
     * 打包为 zip
     */
    public function package(string $name, string $version): string
    {
        $info = get_addon_info($name);
        if (!$info) {
            throw new \RuntimeException('插件不存在');
        }
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            throw new \InvalidArgumentException('版本号格式为 x.y.z');
        }
        $info['version'] = $version;
        $info['name'] = $name;
        set_addon_info($name, $info);

        $addonDir = addon_path($name);
        if (!is_dir($addonDir)) {
            throw new \RuntimeException('插件不存在');
        }
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('未安装 ZipArchive 扩展');
        }

        $tmpDir = runtime_path() . 'addons' . DIRECTORY_SEPARATOR;
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }
        $zipFile = $tmpDir . $name . '-' . $version . '.zip';
        if (is_file($zipFile)) {
            @unlink($zipFile);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('zip open failed');
        }
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($addonDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        $skip = ['.git', '.DS_Store', 'Thumbs.db'];
        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }
            if (in_array($file->getFilename(), $skip, true)) {
                continue;
            }
            $filePath = $file->getRealPath();
            $relative = str_replace('\\', '/', substr($filePath, strlen(realpath($addonDir)) + 1));
            $zip->addFile($filePath, $relative);
        }
        $zip->close();
        return $zipFile;
    }
}
