<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_str_push_config（连接前缀 risk_ + str_push_config）
 * 模拟数据请用 seed：php think seed:run --seed=StrPushConfigSeeder
 */
class CreateStrPushConfig extends Migrator
{
    public function up(): void
    {
        $table = $this->table('str_push_config', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => 'STR/LTR 自动推送全局配置（单行）',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键，固定单行 id=1',
            ])
            ->addColumn('enabled', 'boolean', [
                'null'    => false,
                'default' => true,
                'comment' => 'STR 自动推送总开关：1启用 0停用',
            ])
            ->addColumn('push_by_risk_level', 'boolean', [
                'null'    => false,
                'default' => true,
                'comment' => '按综合风险等级推送：1启用 0停用',
            ])
            ->addColumn('risk_levels', 'text', [
                'null'    => true,
                'comment' => '触发 STR 的风险等级 JSON 数组，如 ["high","critical"]',
            ])
            ->addColumn('push_ltr', 'boolean', [
                'null'    => false,
                'default' => true,
                'comment' => '大额 LTR 自动推送：1启用 0停用',
            ])
            ->addColumn('ltr_threshold_usd', 'integer', [
                'signed'  => false,
                'null'    => false,
                'default' => 10000,
                'comment' => 'LTR 阈值（USD）',
            ])
            ->addColumn('ltr_threshold_hkd', 'integer', [
                'signed'  => false,
                'null'    => false,
                'default' => 50000,
                'comment' => 'LTR 阈值（HKD）',
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
        $this->table('str_push_config')->drop()->save();
    }
}
