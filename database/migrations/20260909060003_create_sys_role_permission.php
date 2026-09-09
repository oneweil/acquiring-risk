<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_sys_role_permission（连接前缀 risk_ + sys_role_permission）
 */
class CreateSysRolePermission extends Migrator
{
    public function up(): void
    {
        $table = $this->table('sys_role_permission', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '角色-权限关联（perm_code 为固定权限码）',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('role_id', 'biginteger', [
                'signed'  => false,
                'null'    => false,
                'comment' => '角色 ID',
            ])
            ->addColumn('perm_code', 'string', [
                'limit'   => 64,
                'null'    => false,
                'comment' => '权限码，如 orders:view',
            ])
            ->addColumn('created_at', 'datetime', [
                'null'    => false,
                'comment' => '创建时间',
            ])
            ->addIndex(['role_id', 'perm_code'], ['unique' => true, 'name' => 'uk_role_perm'])
            ->addIndex(['perm_code'], ['name' => 'idx_perm_code'])
            ->create();
    }

    public function down(): void
    {
        $this->table('sys_role_permission')->drop()->save();
    }
}
