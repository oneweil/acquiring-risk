<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_edd_case（连接前缀 risk_ + edd_case）
 * 模拟数据请用 seed：php think seed:run --seed=EddCaseSeeder
 */
class CreateEddCase extends Migrator
{
    public function up(): void
    {
        $table = $this->table('edd_case', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => 'EDD 强化尽调工单',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('case_no', 'string', [
                'limit'   => 32,
                'null'    => false,
                'comment'  => '业务编号，如 EDD20260908001',
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
            ->addColumn('trigger', 'string', [
                'limit'   => 32,
                'null'    => false,
                'comment'  => '触发原因英文枚举',
            ])
            ->addColumn('risk_level', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'mid',
                'comment'  => '风险等级快照 low/mid/high',
            ])
            ->addColumn('status', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'pending',
                'comment'  => 'pending/collecting/reviewing/passed/rejected/expired',
            ])
            ->addColumn('deadline', 'date', [
                'null'    => false,
                'comment'  => '截止日期',
            ])
            ->addColumn('assignee', 'string', [
                'limit'   => 64,
                'null'    => true,
                'comment'  => '负责人',
            ])
            ->addColumn('progress', 'integer', [
                'signed'  => false,
                'null'    => false,
                'default' => 0,
                'comment'  => '资料完成度 0–100',
            ])
            ->addColumn('checklist', 'text', [
                'null'    => true,
                'comment'  => '勾选清单 JSON：key→bool',
            ])
            ->addColumn('notes', 'string', [
                'limit'   => 500,
                'null'    => true,
                'comment'  => '备注说明',
            ])
            ->addColumn('linked_str_id', 'string', [
                'limit'   => 64,
                'null'    => true,
                'comment'  => '关联 STR 编号（展示用）',
            ])
            ->addColumn('review_remark', 'string', [
                'limit'   => 1000,
                'null'    => true,
                'comment'  => '审核备注',
            ])
            ->addColumn('reviewed_at', 'datetime', [
                'null'    => true,
                'comment'  => '审核时间',
            ])
            ->addColumn('created_at', 'datetime', [
                'null'    => false,
                'comment'  => '创建时间',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null'    => false,
                'comment'  => '最后更新时间',
            ])
            ->addIndex(['case_no'], ['unique' => true, 'name' => 'uk_case_no'])
            ->addIndex(['merchant_id'], ['name' => 'idx_merchant_id'])
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->addIndex(['deadline'], ['name' => 'idx_deadline'])
            ->addIndex(['linked_str_id'], ['name' => 'idx_linked_str_id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('edd_case')->drop()->save();
    }
}
