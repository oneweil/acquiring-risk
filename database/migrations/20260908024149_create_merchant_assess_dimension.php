<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_merchant_assess_dimension（连接前缀 risk_ + merchant_assess_dimension）
 * 模拟数据请用 seed：php think seed:run --seed=MerchantAssessConfigSeeder
 */
class CreateMerchantAssessDimension extends Migrator
{
    public function up(): void
    {
        $table = $this->table('merchant_assess_dimension', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '商户评估维度权重（固定 9 行）',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('dim_key', 'string', [
                'limit'   => 32,
                'null'    => false,
                'comment' => '维度英文 key',
            ])
            ->addColumn('name', 'string', [
                'limit'   => 64,
                'null'    => false,
                'comment' => '维度中文名',
            ])
            ->addColumn('weight', 'integer', [
                'signed'  => false,
                'null'    => false,
                'default' => 0,
                'comment' => '复评权重 %',
            ])
            ->addColumn('onboarding_weight', 'integer', [
                'signed'  => false,
                'null'    => false,
                'default' => 0,
                'comment' => '入网权重 %',
            ])
            ->addColumn('sort', 'integer', [
                'signed'  => false,
                'null'    => false,
                'default' => 0,
                'comment' => '卡片展示顺序，越小越靠前',
            ])
            ->addColumn('created_at', 'datetime', [
                'null'    => false,
                'comment' => '创建时间',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null'    => false,
                'comment' => '最后更新时间',
            ])
            ->addIndex(['dim_key'], ['unique' => true, 'name' => 'uk_dim_key'])
            ->addIndex(['sort'], ['name' => 'idx_sort'])
            ->create();
    }

    public function down(): void
    {
        $this->table('merchant_assess_dimension')->drop()->save();
    }
}
