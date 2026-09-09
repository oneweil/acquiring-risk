<?php

declare(strict_types=1);

use app\model\SysRole;
use app\model\SysUser;
use app\support\PermissionCatalog;
use think\facade\Db;
use think\migration\Seeder;

/**
 * 系统用户 / 角色 / 权限演示数据
 */
class SysRbacSeeder extends Seeder
{
    public function run(): void
    {
        Db::name('sys_role_permission')->delete(true);
        Db::name('sys_role_user')->delete(true);
        Db::name('sys_role')->delete(true);
        Db::name('sys_user')->delete(true);

        $now = date('Y-m-d H:i:s');

        $adminRoleId = (int) Db::name('sys_role')->insertGetId([
            'code'        => PermissionCatalog::ROLE_SYS_ADMIN,
            'name'        => '系统管理员',
            'type'        => SysRole::TYPE_BUILTIN,
            'status'      => SysRole::STATUS_ENABLED,
            'sort'        => 1,
            'description' => '拥有全部菜单与操作权限，不可删除',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $auditorRoleId = (int) Db::name('sys_role')->insertGetId([
            'code'        => PermissionCatalog::ROLE_AUDITOR,
            'name'        => '只读审计',
            'type'        => SysRole::TYPE_BUILTIN,
            'status'      => SysRole::STATUS_ENABLED,
            'sort'        => 90,
            'description' => '仅可查看业务数据与审计日志，不可变更配置',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $riskOpsRoleId = (int) Db::name('sys_role')->insertGetId([
            'code'        => 'RISK_OPS',
            'name'        => '风控专员',
            'type'        => SysRole::TYPE_CUSTOM,
            'status'      => SysRole::STATUS_ENABLED,
            'sort'        => 20,
            'description' => '日常订单监控、预警处置与黑名单维护',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $this->insertPerms($adminRoleId, PermissionCatalog::allCodes(), $now);
        $this->insertPerms($auditorRoleId, PermissionCatalog::auditorCodes(), $now);
        $this->insertPerms($riskOpsRoleId, [
            'dashboard:view',
            'orders:view',
            'orders:export',
            'orders:release',
            'alerts:view',
            'alerts:handle',
            'merchants:view',
            'onboarding:view',
            'blacklist:view',
            'blacklist:edit',
            'rules:view',
            'disposition:view',
            'audit-logs:view',
        ], $now);

        $password = password_hash('admin123', PASSWORD_DEFAULT);

        $adminUserId = (int) Db::name('sys_user')->insertGetId([
            'account'       => 'admin',
            'name'          => '系统管理员',
            'password'      => $password,
            'title'         => '系统管理员',
            'phone'         => '13800000001',
            'email'         => 'admin@risk.local',
            'status'        => SysUser::STATUS_ENABLED,
            'last_login_at' => null,
            'remark'        => '种子超级管理员',
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $opsUserId = (int) Db::name('sys_user')->insertGetId([
            'account'       => 'zhangming',
            'name'          => '张明',
            'password'      => $password,
            'title'         => '风控专员',
            'phone'         => '13800001002',
            'email'         => 'zhangming@risk.local',
            'status'        => SysUser::STATUS_ENABLED,
            'last_login_at' => null,
            'remark'        => '',
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $auditorUserId = (int) Db::name('sys_user')->insertGetId([
            'account'       => 'sunli',
            'name'          => '孙丽',
            'password'      => $password,
            'title'         => '只读审计',
            'phone'         => '13800001010',
            'email'         => 'sunli@risk.local',
            'status'        => SysUser::STATUS_ENABLED,
            'last_login_at' => null,
            'remark'        => '',
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        Db::name('sys_role_user')->insertAll([
            ['role_id' => $adminRoleId, 'user_id' => $adminUserId, 'created_at' => $now],
            ['role_id' => $riskOpsRoleId, 'user_id' => $opsUserId, 'created_at' => $now],
            ['role_id' => $auditorRoleId, 'user_id' => $auditorUserId, 'created_at' => $now],
        ]);
    }

    /**
     * @param list<string> $codes
     */
    private function insertPerms(int $roleId, array $codes, string $now): void
    {
        $rows = [];
        foreach ($codes as $code) {
            $rows[] = [
                'role_id'    => $roleId,
                'perm_code'  => $code,
                'created_at' => $now,
            ];
        }
        if ($rows !== []) {
            Db::name('sys_role_permission')->insertAll($rows);
        }
    }
}
