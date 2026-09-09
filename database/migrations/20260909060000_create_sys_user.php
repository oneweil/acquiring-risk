<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_sys_user（连接前缀 risk_ + sys_user）
 */
class CreateSysUser extends Migrator
{
    public function up(): void
    {
        $table = $this->table('sys_user', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '系统用户',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('account', 'string', [
                'limit'   => 32,
                'null'    => false,
                'comment' => '登录账号：小写字母/数字/下划线 3–32',
            ])
            ->addColumn('name', 'string', [
                'limit'   => 64,
                'null'    => false,
                'comment' => '姓名',
            ])
            ->addColumn('password', 'string', [
                'limit'   => 255,
                'null'    => false,
                'comment' => '密码哈希（password_hash）',
            ])
            ->addColumn('title', 'string', [
                'limit'   => 64,
                'null'    => true,
                'default' => null,
                'comment' => '岗位',
            ])
            ->addColumn('phone', 'string', [
                'limit'   => 32,
                'null'    => true,
                'default' => null,
                'comment' => '手机',
            ])
            ->addColumn('email', 'string', [
                'limit'   => 128,
                'null'    => true,
                'default' => null,
                'comment' => '邮箱',
            ])
            ->addColumn('status', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'enabled',
                'comment' => '状态：enabled/disabled/locked',
            ])
            ->addColumn('last_login_at', 'datetime', [
                'null'    => true,
                'default' => null,
                'comment' => '最近登录时间',
            ])
            ->addColumn('remark', 'string', [
                'limit'   => 255,
                'null'    => true,
                'default' => null,
                'comment' => '备注',
            ])
            ->addColumn('created_at', 'datetime', [
                'null'    => false,
                'comment' => '创建时间',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null'    => false,
                'comment' => '最后更新时间',
            ])
            ->addIndex(['account'], ['unique' => true, 'name' => 'uk_account'])
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->create();
    }

    public function down(): void
    {
        $this->table('sys_user')->drop()->save();
    }
}
