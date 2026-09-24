<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$
//
// Usage: php -S 127.0.0.1:8080 router.php  (cwd = public/)

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

// Serve existing static files / directory indexes as-is
// (e.g. /doc/ → doc/index.html; /yshopadmin/ → yshopadmin/index.html 落地页优先于下方网关)
$file = $_SERVER['DOCUMENT_ROOT'] . $uri;
if ($uri !== '/') {
    if (is_file($file)) {
        return false;
    }
    // Directory request: prefer index.html (Docsify / 落地页), then let PHP built-in server try default indexes
    $dir = rtrim($file, '/\\');
    if (is_dir($dir)) {
        if (is_file($dir . DIRECTORY_SEPARATOR . 'index.html')) {
            header('Content-Type: text/html; charset=utf-8');
            readfile($dir . DIRECTORY_SEPARATOR . 'index.html');
            return true;
        }
        return false;
    }
}

// Admin API gateway: only when no static file/dir matched above
// /yshopadmin/api/* → yshopadmin.php (落地页目录下勿建 api/ 子路径以免遮蔽)
if ($uri === '/yshopadmin' || str_starts_with($uri, '/yshopadmin/')) {
    $pathInfo = $uri === '/yshopadmin' ? '/' : substr($uri, strlen('/yshopadmin'));
    $_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/yshopadmin.php';
    $_SERVER['SCRIPT_NAME']     = '/yshopadmin.php';
    $_SERVER['PATH_INFO']       = $pathInfo === '' ? '/' : $pathInfo;
    require __DIR__ . '/yshopadmin.php';
    return true;
}

// Front
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';
$_SERVER['SCRIPT_NAME']     = '/index.php';
require __DIR__ . '/index.php';
