<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_merchant_level_config（连接前缀 risk_ + merchant_level_config）
 * 模拟数据请用 seed：php think seed:run --seed=MerchantLevelConfigSeeder
 */
class CreateMerchantLevelConfig extends Migrator
{
    public function up(): void
    {
        $table = $this->table('merchant_level_config', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '商户风险等级分值映射（单行配置）',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键，固定单行 id=1',
            ])
            ->addColumn('low_max', 'integer', [
                'signed'  => false,
                'null'    => false,
                'default' => 40,
                'comment' => '低风险上限分值（含）',
            ])
            ->addColumn('mid_max', 'integer', [
                'signed'  => false,
                'null'    => false,
                'default' => 70,
                'comment' => '中风险上限分值（含）',
            ])
            ->addColumn('created_at', 'datetime', [
                'null'    => false,
                'comment' => '创建时间',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null'    => false,
                'comment' => '最后更新时间',
            ])
            ->create();
    }

    public function down(): void
    {
        $this->table('merchant_level_config')->drop()->save();
    }
}
