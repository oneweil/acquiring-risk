<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_sys_role_user（连接前缀 risk_ + sys_role_user）
 */
class CreateSysRoleUser extends Migrator
{
    public function up(): void
    {
        $table = $this->table('sys_role_user', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '角色-用户关联',
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
            ->addColumn('user_id', 'biginteger', [
                'signed'  => false,
                'null'    => false,
                'comment' => '用户 ID',
            ])
            ->addColumn('created_at', 'datetime', [
                'null'    => false,
                'comment' => '创建时间',
            ])
            ->addIndex(['role_id', 'user_id'], ['unique' => true, 'name' => 'uk_role_user'])
            ->addIndex(['user_id'], ['name' => 'idx_user_id'])
            ->addForeignKey('role_id', 'sys_role', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('user_id', 'sys_user', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }

    public function down(): void
    {
        $this->table('sys_role_user')->drop()->save();
    }
}
