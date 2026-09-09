<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_str_report（连接前缀 risk_ + str_report）
 * 模拟数据请用 seed：php think seed:run --seed=StrReportSeeder
 */
class CreateStrReport extends Migrator
{
    public function up(): void
    {
        $table = $this->table('str_report', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => 'STR/LTR 合规报送主表',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('report_no', 'string', [
                'limit'   => 32,
                'null'    => false,
                'comment'  => '业务编号，如 STR20260909001',
            ])
            ->addColumn('type', 'string', [
                'limit'   => 8,
                'null'    => false,
                'comment'  => '报送类型：ltr / str',
            ])
            ->addColumn('trigger_mode', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'manual',
                'comment'  => '触发方式：auto / manual',
            ])
            ->addColumn('merchant_id', 'string', [
                'limit'   => 32,
                'null'    => false,
                'comment'  => '商户号（doopsun merchants.merchantId）',
            ])
            ->addColumn('merchant_name', 'string', [
                'limit'   => 128,
                'null'    => false,
                'default' => '',
                'comment'  => '商户名称快照',
            ])
            ->addColumn('order_no', 'string', [
                'limit'   => 64,
                'null'    => false,
                'comment'  => '关联订单号',
            ])
            ->addColumn('currency', 'string', [
                'limit'   => 8,
                'null'    => false,
                'default' => 'USD',
                'comment'  => '币种：USD / EUR / GBP / HKD',
            ])
            ->addColumn('amount_val', 'decimal', [
                'precision' => 18,
                'scale'     => 2,
                'null'      => false,
                'default'   => '0.00',
                'comment'   => '交易金额数值',
            ])
            ->addColumn('amount_display', 'string', [
                'limit'   => 64,
                'null'    => false,
                'default' => '',
                'comment'  => '金额展示串，如 USD 12,500.00',
            ])
            ->addColumn('trigger_reason', 'string', [
                'limit'   => 512,
                'null'    => false,
                'default' => '',
                'comment'  => '触发原因说明',
            ])
            ->addColumn('suspicious_desc', 'text', [
                'null'    => true,
                'comment'  => '可疑交易描述（type=str 时业务必填）',
            ])
            ->addColumn('status', 'string', [
                'limit'   => 32,
                'null'    => false,
                'default' => 'generated',
                'comment'  => 'pending_confirm/generated/uploaded/submitted/dismissed/archived/rejected',
            ])
            ->addColumn('submitter', 'string', [
                'limit'   => 64,
                'null'    => true,
                'comment'  => '报送人（人工或「系统自动」）',
            ])
            ->addColumn('reviewer', 'string', [
                'limit'   => 64,
                'null'    => true,
                'comment'  => '审核人（确认/排除时写入）',
            ])
            ->addColumn('review_remark', 'string', [
                'limit'   => 1000,
                'null'    => true,
                'comment'  => '确认上报说明',
            ])
            ->addColumn('dismiss_reason', 'string', [
                'limit'   => 1000,
                'null'    => true,
                'comment'  => '无需上报排除理由',
            ])
            ->addColumn('reject_reason', 'string', [
                'limit'   => 1000,
                'null'    => true,
                'comment'  => '监管退回原因（演示/回传用）',
            ])
            ->addColumn('linked_alert_id', 'string', [
                'limit'   => 64,
                'null'    => true,
                'comment'  => '关联预警编号（可空）',
            ])
            ->addColumn('hit_rule_ids', 'text', [
                'null'    => true,
                'comment'  => '命中规则编号 JSON 数组，如 ["R013"]',
            ])
            ->addColumn('reviewed_at', 'datetime', [
                'null'    => true,
                'comment'  => '审核时间（确认/排除）',
            ])
            ->addColumn('submitted_at', 'datetime', [
                'null'    => true,
                'comment'  => '提交监管时间',
            ])
            ->addColumn('created_at', 'datetime', [
                'null'    => false,
                'comment'  => '创建时间',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null'    => false,
                'comment'  => '最后更新时间',
            ])
            ->addIndex(['report_no'], ['unique' => true, 'name' => 'uk_report_no'])
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->addIndex(['type'], ['name' => 'idx_type'])
            ->addIndex(['merchant_id'], ['name' => 'idx_merchant_id'])
            ->addIndex(['order_no'], ['name' => 'idx_order_no'])
            ->addIndex(['created_at'], ['name' => 'idx_created_at'])
            ->create();
    }

    public function down(): void
    {
        $this->table('str_report')->drop()->save();
    }
}
