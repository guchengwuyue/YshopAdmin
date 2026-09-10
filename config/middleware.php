<?php
// 中间件配置
return [
    // 别名或分组
    'alias'    => [
        'auth'       => \app\middleware\Auth::class,
        'permission' => \app\middleware\Permission::class,
        'operlog'    => \app\middleware\OperLog::class,
        'demo'       => \app\middleware\DemoMode::class,
    ],

    // 优先级设置，此数组中的中间件会按照数组中的顺序优先执行
    'priority' => [
        \think\middleware\SessionInit::class,
        \app\middleware\Auth::class,
        \app\middleware\Permission::class,
        \app\middleware\OperLog::class,
    ],
];
