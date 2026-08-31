<?php

declare(strict_types=1);
// 中间件配置
return [
    // 别名或分组
    'alias' => [
        // 后台：Session 初始化（登录页 + 受保护区都需要）
        'session'  => \think\middleware\SessionInit::class,
        // 后台：登录校验（仅受保护区）
        'auth'     => \app\middleware\Auth::class,
        // API：密钥鉴权（仅 /api）
        'api_auth' => \app\middleware\ApiAuth::class,
    ],
    // 优先级设置，此数组中的中间件会按照数组中的顺序优先执行
    'priority' => [
        \think\middleware\SessionInit::class,
        \app\middleware\Auth::class,
        \app\middleware\ApiAuth::class,
    ],
];
