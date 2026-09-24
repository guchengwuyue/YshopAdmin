<?php

return [
    // 管理端 Bearer（adm_）有效期，秒
    'admin_ttl'  => (int) env('TOKEN_ADMIN_TTL', 7200),
    // 站点会员 Bearer（mem_）有效期，秒
    'member_ttl' => (int) env('TOKEN_MEMBER_TTL', 604800),
];
