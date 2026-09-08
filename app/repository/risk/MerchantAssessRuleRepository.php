<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\MerchantAssessRule;
use think\db\exception\DbException;

class MerchantAssessRuleRepository
{
    /**
     * @return list<MerchantAssessRule>
     *
     * @throws DbException
     */
    public function allOrdered(): array
    {
        return MerchantAssessRule::order('category', 'asc')
            ->order('sort', 'asc')
            ->select()
            ->all();
    }

    /**
     * 按 rule_id upsert；主要更新 score / config / enabled（不改 template/description/category/sort）
     *
     * @param list<array{rule_id: string, score: int, config: array<string, mixed>, enabled: bool}> $rules
     *
     * @throws DbException
     */
    public function upsertByRuleId(array $rules): void
    {
        foreach ($rules as $item) {
            $ruleId = (string) ($item['rule_id'] ?? '');
            if ($ruleId === '') {
                continue;
            }

            $model = MerchantAssessRule::where('rule_id', $ruleId)->find();
            if ($model === null) {
                continue;
            }

            $model->save([
                'score'   => (int) ($item['score'] ?? 0),
                'config'  => $item['config'] ?? [],
                'enabled' => !empty($item['enabled']) ? 1 : 0,
            ]);
        }
    }
}
