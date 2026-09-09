<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_alert（连接前缀 risk_ + alert）
 * 模拟数据请用 seed：php think seed:run --seed=AlertSeeder
 */
class CreateAlert extends Migrator
{
    public function up(): void
    {
        $table = $this->table('alert', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '交易预警（订单级，不表示订单挂起待审）',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('alert_no', 'string', [
                'limit'   => 32,
                'null'    => false,
                'comment'  => '业务编号，如 AL202609090001',
            ])
            ->addColumn('scope', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'order',
                'comment'  => '作用域：固定 order',
            ])
            ->addColumn('alerted_at', 'datetime', [
                'null'    => false,
                'comment'  => '预警时间',
            ])
            ->addColumn('merchant_id', 'string', [
                'limit'   => 32,
                'null'    => false,
                'comment'  => '商户号',
            ])
            ->addColumn('order_no', 'string', [
                'limit'   => 64,
                'null'    => true,
                'comment'  => '关联订单号（可空）',
            ])
            ->addColumn('amount_display', 'string', [
                'limit'   => 64,
                'null'    => false,
                'default' => '',
                'comment'  => '金额展示串，如 USD 1,250.00',
            ])
            ->addColumn('risk_level', 'string', [
                'limit'   => 16,
                'null'    => false,
                'comment'  => '风险等级：low / mid / high / critical',
            ])
            ->addColumn('rule_name', 'string', [
                'limit'   => 128,
                'null'    => false,
                'default' => '',
                'comment'  => '主命中规则名称快照',
            ])
            ->addColumn('measure_code', 'string', [
                'limit'   => 32,
                'null'    => false,
                'comment'  => '处置策略编码，如 ALERT_ONLY / CHARGEBACK_INQUIRY',
            ])
            ->addColumn('action_name', 'string', [
                'limit'   => 64,
                'null'    => false,
                'default' => '',
                'comment'  => '处置策略名称快照',
            ])
            ->addColumn('hit_details', 'text', [
                'null'    => true,
                'comment'  => '命中规则详情 JSON 数组',
            ])
            ->addColumn('status', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'pending',
                'comment'  => 'pending / processing / closed',
            ])
            ->addColumn('handle_remark', 'string', [
                'limit'   => 1000,
                'null'    => true,
                'comment'  => '处理备注',
            ])
            ->addColumn('inquiry_desc', 'text', [
                'null'    => true,
                'comment'  => '调单说明',
            ])
            ->addColumn('evaluation_id', 'biginteger', [
                'signed'  => false,
                'null'    => true,
                'comment'  => '关联 order_evaluation.id（可空）',
            ])
            ->addColumn('str_report_id', 'string', [
                'limit'   => 64,
                'null'    => true,
                'comment'  => '关联 STR 报送编号（可空）',
            ])
            ->addColumn('operator_id', 'integer', [
                'signed'  => false,
                'null'    => true,
                'comment'  => '处置人用户 ID（可空）',
            ])
            ->addColumn('handled_at', 'datetime', [
                'null'    => true,
                'comment'  => '处置完成时间',
            ])
            ->addColumn('created_at', 'datetime', [
                'null'    => false,
                'comment'  => '创建时间',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null'    => false,
                'comment'  => '最后更新时间',
            ])
            ->addIndex(['alert_no'], ['unique' => true, 'name' => 'uk_alert_no'])
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->addIndex(['risk_level'], ['name' => 'idx_risk_level'])
            ->addIndex(['measure_code'], ['name' => 'idx_measure_code'])
            ->addIndex(['merchant_id'], ['name' => 'idx_merchant_id'])
            ->addIndex(['order_no'], ['name' => 'idx_order_no'])
            ->addIndex(['alerted_at'], ['name' => 'idx_alerted_at'])
            ->create();
    }

    public function down(): void
    {
        $this->table('alert')->drop()->save();
    }
}
