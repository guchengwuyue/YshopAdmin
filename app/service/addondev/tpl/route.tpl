<?php
declare(strict_types=1);

use think\facade\Route;

return [
    'public' => function () {},
    'auth' => function () {
        Route::get('{$name}/index', '\addons\{$name}\controller\Index@index')
            ->middleware(\app\middleware\Permission::class)
            ->option(['perms' => '{$name}:index:view']);
        Route::get('{$name}/index/list', '\addons\{$name}\controller\Index@list')
            ->middleware(\app\middleware\Permission::class)
            ->option(['perms' => '{$name}:index:list']);
    },
];
