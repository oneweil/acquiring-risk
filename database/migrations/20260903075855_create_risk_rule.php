<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_rule（连接前缀 risk_ + rule）
 * 模拟数据请用 seed：php think seed:run --seed=RuleSeeder
 */
class CreateRiskRule extends Migrator
{
    public function up(): void
    {
        $table = $this->table('rule', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '风控规则配置',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('rule_id', 'string', [
                'limit'   => 16,
                'null'    => false,
                'comment' => '规则编号，如 R001',
            ])
            ->addColumn('category', 'string', [
                'limit'   => 32,
                'null'    => false,
                'comment' => '分类英文枚举',
            ])
            ->addColumn('name', 'string', [
                'limit'   => 128,
                'null'    => false,
                'comment' => '规则名称',
            ])
            ->addColumn('description', 'string', [
                'limit'   => 255,
                'null'    => false,
                'default' => '',
                'comment' => '副文案说明',
            ])
            ->addColumn('content_template', 'string', [
                'limit'   => 512,
                'null'    => false,
                'default' => '',
                'comment' => '展示模板，占位符 {key} 对应 config',
            ])
            ->addColumn('config', 'text', [
                'null'    => true,
                'comment' => '可配阈值 JSON',
            ])
            ->addColumn('measure_code', 'string', [
                'limit'   => 64,
                'null'    => false,
                'comment' => '关联 disposition.code',
            ])
            ->addColumn('enabled', 'boolean', [
                'null'    => false,
                'default' => true,
                'comment' => '是否启用：1启用 0停用',
            ])
            ->addColumn('push_str', 'boolean', [
                'null'    => false,
                'default' => false,
                'comment' => '命中后是否自动推送 STR：1是 0否',
            ])
            ->addColumn('sort', 'integer', [
                'signed'  => false,
                'null'    => false,
                'default' => 0,
                'comment' => '组内排序，越小越靠前',
            ])
            ->addColumn('created_at', 'datetime', [
                'null'    => false,
                'comment' => '创建时间',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null'    => false,
                'comment' => '最后更新时间',
            ])
            ->addIndex(['rule_id'], ['unique' => true, 'name' => 'uk_rule_id'])
            ->addIndex(['category', 'sort'], ['name' => 'idx_category_sort'])
            ->addIndex(['enabled'], ['name' => 'idx_enabled'])
            ->create();
    }

    public function down(): void
    {
        $this->table('rule')->drop()->save();
    }
}
