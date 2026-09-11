<?php
declare(strict_types=1);

namespace app\controller;

use think\Response;

/**
 * 插件静态资源
 */
class AddonAsset extends BaseController
{
    /**
     * 输出 addons/{name}/public 下文件
     */
    public function index(string $name = '', string $path = ''): Response
    {
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name) ?? '';
        $path = str_replace(['..', '\\'], '', $path);
        if ($name === '' || $path === '') {
            return response('Not Found', 404);
        }
        // 控制器路由优先占用时不会到这里；避免把 php 当静态输出
        if (preg_match('/\.php$/i', $path)) {
            return response('Forbidden', 403);
        }
        $file = addon_path($name) . 'public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
        if (!is_file($file)) {
            return response('Not Found', 404);
        }
        $mime = match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            'css' => 'text/css; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'ico' => 'image/x-icon',
            'json' => 'application/json; charset=utf-8',
            'map' => 'application/json; charset=utf-8',
            default => 'application/octet-stream',
        };
        return Response::create((string) file_get_contents($file), 'html', 200)
            ->contentType($mime)
            ->header(['Cache-Control' => 'public, max-age=86400']);
    }
}
