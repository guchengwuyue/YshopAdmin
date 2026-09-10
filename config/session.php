<?php
// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

return [
    // session name
    'name'           => 'PHPSESSID',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // 驱动方式 支持file cache
    'type'           => 'file',
    // 存储连接标识 当type使用cache的时候有效
    'store'          => null,
    // 过期时间（分钟）
    'expire'         => 1440,
    // 前缀
    'prefix'         => '',
    // session 保存路径（空则使用系统临时目录）
    'path'           => runtime_path() . 'session',
];
