<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_order_hit（连接前缀 risk_ + order_hit）
 */
class CreateOrderHit extends Migrator
{
    public function up(): void
    {
        $table = $this->table('order_hit', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '订单规则命中明细',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('evaluation_id', 'biginteger', [
                'signed'  => false,
                'null'    => false,
                'comment' => '关联 order_evaluation.id',
            ])
            ->addColumn('rule_id', 'string', [
                'limit'   => 32,
                'null'    => false,
                'default' => '',
                'comment' => '规则编号 R001…',
            ])
            ->addColumn('rule_name', 'string', [
                'limit'   => 128,
                'null'    => false,
                'default' => '',
                'comment' => '规则名称',
            ])
            ->addColumn('risk_level', 'string', [
                'limit'   => 16,
                'null'    => true,
                'comment' => '命中规则风险等级',
            ])
            ->addColumn('measure', 'string', [
                'limit'   => 64,
                'null'    => true,
                'comment' => '命中时处置措施',
            ])
            ->addColumn('created_at', 'datetime', [
                'null'    => false,
                'comment' => '创建时间',
            ])
            ->addIndex(['evaluation_id'], ['name' => 'idx_evaluation_id'])
            ->addIndex(['rule_id'], ['name' => 'idx_rule_id'])
            ->addForeignKey('evaluation_id', 'order_evaluation', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }

    public function down(): void
    {
        $this->table('order_hit')->drop()->save();
    }
}
