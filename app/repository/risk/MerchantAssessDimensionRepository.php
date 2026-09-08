<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\MerchantAssessDimension;
use think\db\exception\DbException;

class MerchantAssessDimensionRepository
{
    /**
     * @return list<MerchantAssessDimension>
     *
     * @throws DbException
     */
    public function allOrdered(): array
    {
        return MerchantAssessDimension::order('sort', 'asc')->select()->all();
    }

    /**
     * @param array<string, array{weight: int, onboarding_weight: int}> $byKey
     *
     * @throws DbException
     */
    public function upsertBatch(array $byKey): void
    {
        foreach (MerchantAssessDimension::DIM_KEYS as $dimKey) {
            if (!isset($byKey[$dimKey])) {
                continue;
            }
            $item  = $byKey[$dimKey];
            $model = MerchantAssessDimension::where('dim_key', $dimKey)->find();
            $payload = [
                'weight'            => (int) $item['weight'],
                'onboarding_weight' => (int) $item['onboarding_weight'],
            ];

            if ($model === null) {
                MerchantAssessDimension::create(array_merge([
                    'dim_key' => $dimKey,
                    'name'    => MerchantAssessDimension::DIM_LABELS[$dimKey] ?? $dimKey,
                    'sort'    => (array_search($dimKey, MerchantAssessDimension::DIM_KEYS, true) + 1) * 10,
                ], $payload));
            } else {
                $model->save($payload);
            }
        }
    }
}
