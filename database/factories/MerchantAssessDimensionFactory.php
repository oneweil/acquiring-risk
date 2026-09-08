<?php

declare(strict_types=1);

namespace database\factories;

/**
 * 商户评估维度默认数据（9 维双权重）
 */
class MerchantAssessDimensionFactory
{
    /**
     * @return list<array<string, mixed>>
     */
    public function builtinRows(): array
    {
        $now = date('Y-m-d H:i:s');
        $rows = [];

        foreach ($this->definitions() as $def) {
            $rows[] = [
                'dim_key'            => $def['dim_key'],
                'name'               => $def['name'],
                'weight'             => $def['weight'],
                'onboarding_weight'  => $def['onboarding_weight'],
                'sort'               => $def['sort'],
                'created_at'         => $now,
                'updated_at'         => $now,
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{dim_key: string, name: string, weight: int, onboarding_weight: int, sort: int}>
     */
    private function definitions(): array
    {
        return [
            ['dim_key' => 'industry', 'name' => '行业风险', 'weight' => 17, 'onboarding_weight' => 34, 'sort' => 10],
            ['dim_key' => 'chargeback', 'name' => '拒付率', 'weight' => 19, 'onboarding_weight' => 0, 'sort' => 20],
            ['dim_key' => 'fraud', 'name' => '欺诈率', 'weight' => 17, 'onboarding_weight' => 0, 'sort' => 30],
            ['dim_key' => 'tenure', 'name' => '经营时长', 'weight' => 8, 'onboarding_weight' => 16, 'sort' => 40],
            ['dim_key' => 'geo', 'name' => '注册地风险', 'weight' => 8, 'onboarding_weight' => 16, 'sort' => 50],
            ['dim_key' => 'website', 'name' => '网站合规', 'weight' => 10, 'onboarding_weight' => 20, 'sort' => 60],
            ['dim_key' => 'compliance', 'name' => '合规筛查', 'weight' => 7, 'onboarding_weight' => 14, 'sort' => 70],
            ['dim_key' => 'refund', 'name' => '退款率', 'weight' => 10, 'onboarding_weight' => 0, 'sort' => 80],
            ['dim_key' => 'volumeAnomaly', 'name' => '交易放量', 'weight' => 4, 'onboarding_weight' => 0, 'sort' => 90],
        ];
    }
}
