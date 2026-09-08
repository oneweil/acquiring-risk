<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_merchant_level_policy（连接前缀 risk_ + merchant_level_policy）
 * 模拟数据请用 seed：php think seed:run --seed=MerchantLevelConfigSeeder
 */
class CreateMerchantLevelPolicy extends Migrator
{
    public function up(): void
    {
        $table = $this->table('merchant_level_policy', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '商户风险等级权益策略（固定三档）',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('level', 'string', [
                'limit'   => 16,
                'null'    => false,
                'comment' => '风险等级英文枚举：low/mid/high',
            ])
            ->addColumn('settle_days', 'integer', [
                'signed'  => false,
                'null'    => false,
                'comment' => '结算周期天数 N（展示为 T+N）',
            ])
            ->addColumn('margin_rate', 'integer', [
                'signed'  => false,
                'null'    => false,
                'comment' => '滚动保证金百分数整数（如 3 表示 3%）',
            ])
            ->addColumn('single_limit', 'integer', [
                'signed'  => false,
                'null'    => false,
                'comment' => '单笔限额（USD 整数）',
            ])
            ->addColumn('daily_limit', 'integer', [
                'signed'  => false,
                'null'    => false,
                'comment' => '日累计限额（USD 整数）',
            ])
            ->addColumn('review_cycle', 'string', [
                'limit'   => 16,
                'null'    => false,
                'comment' => '复评频率英文：quarterly/monthly/biweekly/weekly',
            ])
            ->addColumn('created_at', 'datetime', [
                'null'    => false,
                'comment' => '创建时间',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null'    => false,
                'comment' => '最后更新时间',
            ])
            ->addIndex(['level'], ['unique' => true, 'name' => 'uk_level'])
            ->create();
    }

    public function down(): void
    {
        $this->table('merchant_level_policy')->drop()->save();
    }
}
