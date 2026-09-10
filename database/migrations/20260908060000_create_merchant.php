<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_merchant（连接前缀 risk_ + merchant）
 * 主站推送投影；模拟数据：php think seed:run --seed=MerchantSeeder
 */
class CreateMerchant extends Migrator
{
    public function up(): void
    {
        $table = $this->table('merchant', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '商户投影（主站推送；非入网审核主数据）',
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
                'comment' => '外部商户号',
            ])
            ->addColumn('name', 'string', [
                'limit'   => 200,
                'null'    => false,
                'default' => '',
                'comment' => '商户名称',
            ])
            ->addColumn('status', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'not_opened',
                'comment' => '收单状态 normal/watch/restricted/suspended/not_opened',
            ])
            ->addColumn('industry', 'string', [
                'limit'   => 64,
                'null'    => true,
                'comment' => '行业',
            ])
            ->addColumn('country', 'string', [
                'limit'   => 8,
                'null'    => true,
                'comment' => '注册国家代码',
            ])
            ->addColumn('register_at', 'date', [
                'null'    => true,
                'comment' => '注册日期',
            ])
            ->addColumn('onboard_at', 'date', [
                'null'    => true,
                'comment' => '入网日期',
            ])
            ->addColumn('website', 'string', [
                'limit'   => 255,
                'null'    => true,
                'comment' => '网站',
            ])
            ->addColumn('email', 'string', [
                'limit'   => 128,
                'null'    => true,
                'comment' => '联系邮箱',
            ])
            ->addColumn('mobile', 'string', [
                'limit'   => 32,
                'null'    => true,
                'comment' => '联系电话',
            ])
            ->addColumn('address', 'string', [
                'limit'   => 255,
                'null'    => true,
                'comment' => '地址',
            ])
            ->addColumn('website_status', 'string', [
                'limit'   => 32,
                'null'    => true,
                'comment' => 'compliant/mismatch/unverified',
            ])
            ->addColumn('compliance_hits', 'integer', [
                'signed'  => false,
                'null'    => true,
                'comment' => '制裁/PEP 命中条数',
            ])
            ->addColumn('review_status', 'string', [
                'limit'   => 16,
                'null'    => true,
                'comment' => '主站审核快照 approved/rejected',
            ])
            ->addColumn('source_version', 'biginteger', [
                'signed'  => false,
                'null'    => false,
                'default' => 0,
                'comment' => '主站版本/时间戳，乱序保护',
            ])
            ->addColumn('extra', 'text', [
                'null'    => true,
                'comment' => '扩展 JSON',
            ])
            ->addColumn('synced_at', 'datetime', [
                'null'    => false,
                'comment' => '最近成功写入投影时间',
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
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->addIndex(['industry'], ['name' => 'idx_industry'])
            ->addIndex(['country'], ['name' => 'idx_country'])
            ->addIndex(['review_status'], ['name' => 'idx_review_status'])
            ->create();
    }

    public function down(): void
    {
        $this->table('merchant')->drop()->save();
    }
}
