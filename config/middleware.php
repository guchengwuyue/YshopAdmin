<?php
// 中间件配置
return [
    // 别名或分组
    'alias'    => [
        'auth'       => \app\middleware\Auth::class,
        'permission' => \app\middleware\Permission::class,
        'operlog'    => \app\middleware\OperLog::class,
        'demo'       => \app\middleware\DemoMode::class,
        'cors'       => \app\middleware\Cors::class,
        'json'       => \app\middleware\JsonOnly::class,
        'adminToken' => \app\middleware\AdminToken::class,
        'memberToken'=> \app\middleware\MemberToken::class,
    ],

    // 优先级设置，此数组中的中间件会按照数组中的顺序优先执行
    'priority' => [
        \app\middleware\Cors::class,
        \think\middleware\SessionInit::class,
        \app\middleware\JsonOnly::class,
        \app\middleware\AdminToken::class,
        \app\middleware\MemberToken::class,
        \app\middleware\Auth::class,
        \app\middleware\Permission::class,
        \app\middleware\OperLog::class,
    ],
];
