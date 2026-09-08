<?php

declare(strict_types=1);

namespace database\factories;

/**
 * 商户风险等级配置默认数据
 */
class MerchantLevelConfigFactory
{
    /**
     * 分值映射单行（id=1）
     *
     * @return array<string, mixed>
     */
    public function builtinConfig(): array
    {
        $now = date('Y-m-d H:i:s');

        return [
            'id'         => 1,
            'low_max'    => 40,
            'mid_max'    => 70,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * 三档权益默认行
     *
     * @return list<array<string, mixed>>
     */
    public function builtinPolicies(): array
    {
        $now = date('Y-m-d H:i:s');

        return [
            [
                'level'         => 'low',
                'settle_days'   => 3,
                'margin_rate'   => 3,
                'single_limit'  => 50000,
                'daily_limit'   => 200000,
                'review_cycle'  => 'quarterly',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'level'         => 'mid',
                'settle_days'   => 7,
                'margin_rate'   => 5,
                'single_limit'  => 10000,
                'daily_limit'   => 50000,
                'review_cycle'  => 'monthly',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'level'         => 'high',
                'settle_days'   => 14,
                'margin_rate'   => 10,
                'single_limit'  => 2000,
                'daily_limit'   => 10000,
                'review_cycle'  => 'biweekly',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ];
    }
}
