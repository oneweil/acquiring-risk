<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\MerchantLevelPolicy;
use think\db\exception\DbException;

class MerchantLevelPolicyRepository
{
    /**
     * 按 LEVELS 顺序返回，key=level
     *
     * @return array<string, MerchantLevelPolicy>
     *
     * @throws DbException
     */
    public function allByLevel(): array
    {
        $byLevel = [];
        foreach (MerchantLevelPolicy::select()->all() as $row) {
            $byLevel[(string) $row->level] = $row;
        }

        $result = [];
        foreach (MerchantLevelPolicy::LEVELS as $level) {
            if (isset($byLevel[$level])) {
                $result[$level] = $byLevel[$level];
            }
        }

        return $result;
    }

    /**
     * @param array<string, array{settle_days: int, margin_rate: int, single_limit: int, daily_limit: int, review_cycle: string}> $policies
     *
     * @throws DbException
     */
    public function upsertBatch(array $policies): void
    {
        foreach (MerchantLevelPolicy::LEVELS as $level) {
            if (!isset($policies[$level])) {
                continue;
            }
            $item = $policies[$level];
            $model = MerchantLevelPolicy::where('level', $level)->find();
            $payload = [
                'settle_days'  => $item['settle_days'],
                'margin_rate'  => $item['margin_rate'],
                'single_limit' => $item['single_limit'],
                'daily_limit'  => $item['daily_limit'],
                'review_cycle' => $item['review_cycle'],
            ];

            if ($model === null) {
                MerchantLevelPolicy::create(array_merge(['level' => $level], $payload));
            } else {
                $model->save($payload);
            }
        }
    }
}
