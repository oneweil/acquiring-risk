<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_blacklist（连接前缀 risk_ + blacklist）
 * 模拟数据请用 seed：php think seed:run
 */
class CreateRiskBlacklist extends Migrator
{
    public function up(): void
    {
        $table = $this->table('blacklist', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '风控黑名单',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('code', 'string', [
                'limit'   => 32,
                'null'    => false,
                'comment' => '业务编号，如 BL000001，列表展示用',
            ])
            ->addColumn('type', 'string', [
                'limit'   => 32,
                'null'    => false,
                'comment' => '类型英文枚举：ip/email/card/country/website/phone',
            ])
            ->addColumn('value', 'string', [
                'limit'   => 128,
                'null'    => false,
                'comment' => '黑名单值，如 IP、脱敏卡号、邮箱等',
            ])
            ->addColumn('reason', 'string', [
                'limit'   => 255,
                'null'    => false,
                'comment' => '加入原因说明',
            ])
            ->addColumn('risk_level', 'string', [
                'limit'   => 16,
                'null'    => false,
                'comment' => '风险等级英文枚举：low/medium/high/critical',
            ])
            ->addColumn('effective_date', 'date', [
                'null'    => false,
                'comment' => '生效日期',
            ])
            ->addColumn('expiry_date', 'date', [
                'null'    => true,
                'default' => null,
                'comment' => '到期日期；NULL 表示长期有效',
            ])
            ->addColumn('status', 'boolean', [
                'null'    => false,
                'default' => true,
                'comment' => '是否生效：1生效 0失效',
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
            ->addIndex(['type', 'value'], ['name' => 'idx_type_value'])
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->addIndex(['expiry_date'], ['name' => 'idx_expiry_date'])
            ->create();
    }

    public function down(): void
    {
        $this->table('blacklist')->drop()->save();
    }
}
