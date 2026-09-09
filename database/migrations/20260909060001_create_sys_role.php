<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_sys_role（连接前缀 risk_ + sys_role）
 */
class CreateSysRole extends Migrator
{
    public function up(): void
    {
        $table = $this->table('sys_role', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '系统角色',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('code', 'string', [
                'limit'   => 64,
                'null'    => false,
                'comment' => '角色编码：大写字母/下划线',
            ])
            ->addColumn('name', 'string', [
                'limit'   => 64,
                'null'    => false,
                'comment' => '角色名称',
            ])
            ->addColumn('type', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'custom',
                'comment' => '类型：builtin/custom',
            ])
            ->addColumn('status', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'enabled',
                'comment' => '状态：enabled/disabled',
            ])
            ->addColumn('sort', 'integer', [
                'signed'  => false,
                'null'    => false,
                'default' => 100,
                'comment' => '排序，越小越靠前',
            ])
            ->addColumn('description', 'string', [
                'limit'   => 255,
                'null'    => true,
                'default' => null,
                'comment' => '职责说明',
            ])
            ->addColumn('created_at', 'datetime', [
                'null'    => false,
                'comment' => '创建时间',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null'    => false,
                'comment' => '最后更新时间',
            ])
            ->addIndex(['code'], ['unique' => true, 'name' => 'uk_code'])
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->addIndex(['sort'], ['name' => 'idx_sort'])
            ->create();
    }

    public function down(): void
    {
        $this->table('sys_role')->drop()->save();
    }
}
