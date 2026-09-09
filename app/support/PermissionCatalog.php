<?php

declare(strict_types=1);

namespace app\support;

/**
 * 固定权限目录（无 sys_permission 表；与原型 PERM_MODULES / 数据字典对齐）
 */
final class PermissionCatalog
{
    public const ROLE_SYS_ADMIN = 'SYS_ADMIN';

    public const ROLE_AUDITOR = 'AUDITOR';

    /**
     * @return list<array{id: string, name: string, perms: list<array{id: string, name: string}>}>
     */
    public static function modules(): array
    {
        return [
            [
                'id'    => 'monitor',
                'name'  => '监控中心',
                'perms' => [
                    ['id' => 'dashboard:view', 'name' => '风控总览-查看'],
                    ['id' => 'orders:view', 'name' => '订单监控-查看'],
                    ['id' => 'orders:export', 'name' => '订单监控-导出'],
                    ['id' => 'orders:release', 'name' => '订单放行/取消拦截'],
                    ['id' => 'alerts:view', 'name' => '预警中心-查看'],
                    ['id' => 'alerts:handle', 'name' => '预警处置'],
                ],
            ],
            [
                'id'    => 'merchant',
                'name'  => '商户管理',
                'perms' => [
                    ['id' => 'onboarding:view', 'name' => '商户入网-查看'],
                    ['id' => 'onboarding:review', 'name' => '商户入网-审核'],
                    ['id' => 'merchants:view', 'name' => '商户列表-查看'],
                    ['id' => 'merchants:edit', 'name' => '商户信息-编辑'],
                    ['id' => 'merchant-risk:view', 'name' => '评估规则-查看'],
                    ['id' => 'merchant-risk:edit', 'name' => '评估规则-配置'],
                    ['id' => 'merchant-risk-level:edit', 'name' => '风险等级规则-配置'],
                ],
            ],
            [
                'id'    => 'compliance',
                'name'  => '合规报送',
                'perms' => [
                    ['id' => 'str:view', 'name' => 'STR报送-查看'],
                    ['id' => 'str:confirm', 'name' => 'STR确认/上报'],
                    ['id' => 'str:push-rules', 'name' => 'STR推送规则-配置'],
                    ['id' => 'edd:view', 'name' => 'EDD-查看'],
                    ['id' => 'edd:manage', 'name' => 'EDD工单处理'],
                ],
            ],
            [
                'id'    => 'rules',
                'name'  => '规则配置',
                'perms' => [
                    ['id' => 'rules:view', 'name' => '风控规则-查看'],
                    ['id' => 'rules:edit', 'name' => '风控规则-编辑'],
                    ['id' => 'disposition:view', 'name' => '处置策略-查看'],
                    ['id' => 'disposition:edit', 'name' => '处置策略-编辑'],
                    ['id' => 'blacklist:view', 'name' => '黑名单-查看'],
                    ['id' => 'blacklist:edit', 'name' => '黑名单-维护'],
                ],
            ],
            [
                'id'    => 'system',
                'name'  => '系统管理',
                'perms' => [
                    ['id' => 'users:view', 'name' => '用户管理-查看'],
                    ['id' => 'users:edit', 'name' => '用户管理-管理'],
                    ['id' => 'roles:view', 'name' => '角色权限-查看'],
                    ['id' => 'roles:edit', 'name' => '角色权限-管理'],
                    ['id' => 'audit-logs:view', 'name' => '日志记录-查看'],
                    ['id' => 'audit-logs:export', 'name' => '日志记录-导出'],
                ],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function allCodes(): array
    {
        $codes = [];
        foreach (self::modules() as $module) {
            foreach ($module['perms'] as $perm) {
                $codes[] = $perm['id'];
            }
        }

        return $codes;
    }

    public static function isValid(string $code): bool
    {
        return in_array($code, self::allCodes(), true);
    }

    /**
     * 只读审计默认权限（与原型 AUDITOR 对齐）
     *
     * @return list<string>
     */
    public static function auditorCodes(): array
    {
        return [
            'dashboard:view',
            'orders:view',
            'alerts:view',
            'onboarding:view',
            'merchants:view',
            'merchant-risk:view',
            'str:view',
            'edd:view',
            'rules:view',
            'disposition:view',
            'blacklist:view',
            'users:view',
            'roles:view',
            'audit-logs:view',
            'audit-logs:export',
        ];
    }
}
