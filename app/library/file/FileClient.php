<?php
declare(strict_types=1);

namespace app\library\file;

use app\service\file\ConfigService;
use app\service\file\FileService;
use think\file\UploadedFile;

/**
 * 对外文件上传入口（读主配置）
 */
class FileClient
{
    /**
     * 使用主配置上传文件
     * @param UploadedFile|string $file 上传对象或本地路径
     * @param string $dir 对象前缀目录
     * @return array{id:int,name:string,path:string,url:string,size:int,type:string,groupId:int}
     */
    public static function upload(UploadedFile|string $file, string $dir = ''): array
    {
        $service = new FileService();
        return $service->uploadByMaster($file, $dir);
    }

    /**
     * 主配置驱动连通测试（写/读/删临时对象）
     */
    public static function testMaster(): void
    {
        $cfg = (new ConfigService())->getMasterRaw();
        if (!$cfg) {
            throw new \RuntimeException('请先设置主配置');
        }
        (new ConfigService())->testConnection($cfg);
    }
}
