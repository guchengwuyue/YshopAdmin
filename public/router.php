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

// Serve existing static files as-is
$file = $_SERVER['DOCUMENT_ROOT'] . $uri;
if ($uri !== '/' && is_file($file)) {
    return false;
}

// Admin: /yshopadmin → yshopadmin.php with PATH_INFO without prefix
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
