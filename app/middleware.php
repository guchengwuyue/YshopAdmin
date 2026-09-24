<?php
// 全局中间件定义文件
return [
    \app\middleware\Cors::class,
    \think\middleware\SessionInit::class,
];
