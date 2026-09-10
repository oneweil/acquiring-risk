<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_disposition（连接前缀 risk_ + disposition）
 * 模拟数据请用 seed：php think seed:run --seed=DispositionSeeder
 */
class CreateRiskDisposition extends Migrator
{
    public function up(): void
    {
        $table = $this->table('disposition', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '风控处置策略',
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
                'comment' => '策略编码，如 DECLINE',
            ])
            ->addColumn('name', 'string', [
                'limit'   => 64,
                'null'    => false,
                'comment' => '策略中文名',
            ])
            ->addColumn('description', 'string', [
                'limit'   => 255,
                'null'    => false,
                'default' => '',
                'comment' => '说明',
            ])
            ->addColumn('risk_level', 'string', [
                'limit'   => 16,
                'null'    => false,
                'comment' => '风险等级英文枚举：low/mid/high/critical',
            ])
            ->addColumn('scope', 'string', [
                'limit'   => 16,
                'null'    => false,
                'comment' => 'transaction=本笔决策；merchant=商户状态动作（非人审队列）',
            ])
            ->addColumn('priority', 'integer', [
                'signed'  => false,
                'null'    => false,
                'default' => 100,
                'comment' => '优先级，数值越小越优先',
            ])
            ->addColumn('is_block', 'boolean', [
                'null'    => false,
                'default' => false,
                'comment' => '是否阻断本笔交易：1→decline；0→pass（3DS 另映射）',
            ])
            ->addColumn('push_alert', 'boolean', [
                'null'    => false,
                'default' => true,
                'comment' => '是否写订单预警 risk_alert：1是 0否',
            ])
            ->addColumn('status', 'boolean', [
                'null'    => false,
                'default' => true,
                'comment' => '是否启用：1启用 0停用',
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
            ->addIndex(['scope', 'status'], ['name' => 'idx_scope_status'])
            ->addIndex(['priority'], ['name' => 'idx_priority'])
            ->addIndex(['risk_level'], ['name' => 'idx_risk_level'])
            ->create();
    }

    public function down(): void
    {
        $this->table('disposition')->drop()->save();
    }
}
