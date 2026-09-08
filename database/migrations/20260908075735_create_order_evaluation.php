<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_order_evaluation（连接前缀 risk_ + order_evaluation）
 */
class CreateOrderEvaluation extends Migrator
{
    public function up(): void
    {
        $table = $this->table('order_evaluation', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '订单风控评估结果',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('order_no', 'string', [
                'limit'   => 64,
                'null'    => false,
                'comment' => '商户订单号，对应 doopsun_order.orderid',
            ])
            ->addColumn('doopsun_order_id', 'string', [
                'limit'   => 64,
                'null'    => true,
                'comment' => '通道订单号，对应 doopsun_order.doopsun_orderid',
            ])
            ->addColumn('merchant_id', 'string', [
                'limit'   => 32,
                'null'    => false,
                'default' => '',
                'comment' => '商户号',
            ])
            ->addColumn('risk_level', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'low',
                'comment' => '风险等级 low/mid/high/critical',
            ])
            ->addColumn('action', 'string', [
                'limit'   => 64,
                'null'    => true,
                'comment' => '最终处置策略名',
            ])
            ->addColumn('measure_code', 'string', [
                'limit'   => 32,
                'null'    => true,
                'comment' => '处置策略编码 DECLINE/ALERT_ONLY 等',
            ])
            ->addColumn('decision', 'string', [
                'limit'   => 32,
                'null'    => false,
                'default' => 'pass',
                'comment' => '决策 pass/decline/challenge_3ds',
            ])
            ->addColumn('evaluated_at', 'datetime', [
                'null'    => false,
                'comment' => '评估时间',
            ])
            ->addColumn('created_at', 'datetime', [
                'null'    => false,
                'comment' => '创建时间',
            ])
            ->addIndex(['order_no'], ['unique' => true, 'name' => 'uk_order_no'])
            ->addIndex(['merchant_id'], ['name' => 'idx_merchant_id'])
            ->addIndex(['risk_level'], ['name' => 'idx_risk_level'])
            ->addIndex(['decision'], ['name' => 'idx_decision'])
            ->addIndex(['evaluated_at'], ['name' => 'idx_evaluated_at'])
            ->create();
    }

    public function down(): void
    {
        $this->table('order_evaluation')->drop()->save();
    }
}
