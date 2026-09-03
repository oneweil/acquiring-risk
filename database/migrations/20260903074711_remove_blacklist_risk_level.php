<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 黑名单表去掉 risk_level 字段
 */
class RemoveBlacklistRiskLevel extends Migrator
{
    public function up(): void
    {
        $table = $this->table('blacklist');
        if ($table->hasColumn('risk_level')) {
            $table->removeColumn('risk_level')->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('blacklist');
        if (!$table->hasColumn('risk_level')) {
            $table->addColumn('risk_level', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'high',
                'comment' => '风险等级英文枚举：low/medium/high/critical',
                'after'   => 'reason',
            ])->update();
        }
    }
}
