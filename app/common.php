<?php

declare(strict_types=1);
// 应用公共文件

/**
 * 是否已登录后台（Session 含 username）
 */
function admin_is_logged_in(): bool
{
    $user = session('admin_user');

    return is_array($user) && !empty($user['username']);
}

/**
 * 当前登录用户 Session 数组（未登录返回 null）
 *
 * @return array<string, mixed>|null
 */
function admin_user(): ?array
{
    $user = session('admin_user');

    return is_array($user) && !empty($user['username']) ? $user : null;
}

/**
 * 当前用户是否拥有权限码（SYS_ADMIN 全放行）
 */
function admin_can(string $permCode): bool
{
    return (new \app\service\risk\PermissionService())->can(admin_user(), $permCode);
}

/**
 * 风险等级 Badge 样式（列表页通用）
 */
function risk_level_badge(string $level): string
{
    return match ($level) {
        '极高风险' => 'bg-purple text-purple-fg',
        '高风险'   => 'bg-red text-red-fg',
        '中风险'   => 'bg-yellow text-yellow-fg',
        default    => 'bg-green text-green-fg',
    };
}

/**
 * 商户名称缩写（表格 Avatar 展示）
 */
function merchant_initials(string $name): string
{
    $name = trim($name);
    if ($name === '') {
        return '—';
    }

    $parts = preg_split('/\s+/u', $name) ?: [];
    if (count($parts) >= 2) {
        return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
    }

    return mb_strtoupper(mb_substr($name, 0, 2));
}

/**
 * 风险等级 Badge（浅色，Employee 表格风格）
 */
function risk_level_badge_lt(string $level): string
{
    return match ($level) {
        '极高风险' => 'bg-purple-lt',
        '高风险'   => 'bg-red-lt',
        '中风险'   => 'bg-yellow-lt',
        default    => 'bg-green-lt',
    };
}

/**
 * 商户 Avatar 配色（Employee 表格风格）
 */
function merchant_avatar_class(int $index): string
{
    $classes = [
        'bg-primary-lt text-primary',
        'bg-azure-lt text-azure',
        'bg-indigo-lt text-indigo',
        'bg-purple-lt text-purple',
        'bg-teal-lt text-teal',
    ];

    return $classes[($index - 1) % count($classes)];
}

/**
 * 订单状态 Badge（浅色，Employee 表格风格）
 */
function order_status_badge_lt(string $status): string
{
    return match ($status) {
        '成功'   => 'bg-green-lt',
        '拦截'   => 'bg-red-lt',
        '审核中' => 'bg-yellow-lt',
        default  => 'bg-secondary-lt',
    };
}

/**
 * 订单/业务状态 Badge 样式（列表页通用）
 */
function order_status_badge(string $status): string
{
    return match ($status) {
        '成功'   => 'bg-green text-green-fg',
        '拦截'   => 'bg-red text-red-fg',
        '审核中' => 'bg-yellow text-yellow-fg',
        '失败'   => 'bg-secondary text-secondary-fg',
        default  => 'bg-secondary text-secondary-fg',
    };
}
