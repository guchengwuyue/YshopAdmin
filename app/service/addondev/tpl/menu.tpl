<?php
declare(strict_types=1);

/**
 * 菜单配置（SPA：path + component）
 */
return [
    [
        'menu_name' => '{$title}',
        'parent_id' => 0,
        'order_num' => 100,
        'url'       => '#',
        'path'      => '/{$name}',
        'component' => 'Layout',
        'menu_type' => 'M',
        'visible'   => '0',
        'perms'     => '',
        'icon'      => 'fa fa-folder-o',
        'remark'    => 'addon:{$name}',
        'children'  => [
            [
                'menu_name' => '首页',
                'order_num' => 1,
                'url'       => '/{$name}/index',
                'path'      => 'index',
                'component' => '{$name}/index/index',
                'menu_type' => 'C',
                'visible'   => '0',
                'perms'     => '{$name}:index:view',
                'icon'      => 'fa fa-circle-o',
                'remark'    => 'addon:{$name}',
                'children'  => [
                    [
                        'menu_name' => '列表',
                        'menu_type' => 'F',
                        'perms'     => '{$name}:index:list',
                        'url'       => '#',
                        'remark'    => 'addon:{$name}',
                    ],
                ],
            ],
        ],
    ],
];
