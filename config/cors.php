<?php

return [
    // * 或逗号分隔的 Origin 列表（Vite 开发源需写具体域名）
    'origins'       => (string) env('CORS_ORIGINS', '*'),
    'allow_headers' => 'Authorization, Content-Type, X-Requested-With, Accept, Origin',
    'allow_methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
    'max_age'       => 86400,
];
