<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_merchant_assessment（连接前缀 risk_ + merchant_assessment）
 * 模拟数据请用 seed：php think seed:run --seed=MerchantAssessmentSeeder
 */
class CreateMerchantAssessment extends Migrator
{
    public function up(): void
    {
        $table = $this->table('merchant_assessment', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '商户评估结果（每商户最新一条）',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('merchant_id', 'string', [
                'limit'   => 32,
                'null'    => false,
                'comment' => '外部商户号，对应 risk_merchant.merchant_id',
            ])
            ->addColumn('risk_score', 'integer', [
                'signed'  => false,
                'null'    => false,
                'default' => 0,
                'comment' => '综合评分 0–100',
            ])
            ->addColumn('risk_level', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'low',
                'comment' => '风险等级 low/mid/high',
            ])
            ->addColumn('assess_type', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'periodic',
                'comment' => '评估类型 onboarding/periodic',
            ])
            ->addColumn('website_status', 'string', [
                'limit'   => 32,
                'null'    => true,
                'comment' => '网站合规：compliant/mismatch/unverified',
            ])
            ->addColumn('compliance_hits', 'integer', [
                'signed'  => false,
                'null'    => true,
                'comment' => '制裁/PEP 命中条数',
            ])
            ->addColumn('assess_details', 'text', [
                'null'    => true,
                'comment' => '维度明细 JSON：name/weight/raw/weighted/rule_content',
            ])
            ->addColumn('assessed_at', 'datetime', [
                'null'    => false,
                'comment' => '最近评估时间',
            ])
            ->addColumn('created_at', 'datetime', [
                'null'    => false,
                'comment' => '创建时间',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null'    => false,
                'comment' => '最后更新时间',
            ])
            ->addIndex(['merchant_id'], ['unique' => true, 'name' => 'uk_merchant_id'])
            ->addIndex(['risk_level'], ['name' => 'idx_risk_level'])
            ->addIndex(['assessed_at'], ['name' => 'idx_assessed_at'])
            ->create();
    }

    public function down(): void
    {
        $this->table('merchant_assessment')->drop()->save();
    }
}
