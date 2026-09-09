<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * risk_rule 增加 push_str：命中该规则时是否自动推送 STR
 */
class AddPushStrToRule extends Migrator
{
    public function up(): void
    {
        $table = $this->table('rule');
        if (!$table->hasColumn('push_str')) {
            $table->addColumn('push_str', 'boolean', [
                'null'    => false,
                'default' => false,
                'comment' => '命中后是否自动推送 STR：1是 0否',
                'after'   => 'enabled',
            ])->update();
        }

        // 按默认策略回填：默认类别，或处置策略风险等级为 high/critical
        $defaultCategories = ['fraud', 'geo_sanctions', 'blacklist', 'merchant'];
        $highLevels       = ['high', 'critical'];

        $prefix = (string) (config('database.connections.mysql.prefix') ?: '');
        $ruleTable = $prefix . 'rule';
        $dispTable = $prefix . 'disposition';

        $escapedCats = array_map(static fn (string $c): string => "'" . str_replace("'", "''", $c) . "'", $defaultCategories);
        $escapedLvls = array_map(static fn (string $c): string => "'" . str_replace("'", "''", $c) . "'", $highLevels);

        $sql = sprintf(
            'UPDATE `%s` r LEFT JOIN `%s` d ON d.`code` = r.`measure_code`
             SET r.`push_str` = 1
             WHERE r.`category` IN (%s) OR d.`risk_level` IN (%s)',
            $ruleTable,
            $dispTable,
            implode(',', $escapedCats),
            implode(',', $escapedLvls)
        );
        $this->execute($sql);
    }

    public function down(): void
    {
        $table = $this->table('rule');
        if ($table->hasColumn('push_str')) {
            $table->removeColumn('push_str')->update();
        }
    }
}
