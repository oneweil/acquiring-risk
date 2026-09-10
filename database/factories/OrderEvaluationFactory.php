<?php

declare(strict_types=1);

namespace database\factories;

use app\model\Alert;
use app\model\OrderEvaluation;

/**
 * 订单评估 + 命中明细演示数据（与 AlertFactory::demoSet 的 order_no 对齐）
 */
class OrderEvaluationFactory
{
    /**
     * @return list<array{evaluation: array<string, mixed>, hits: list<array<string, mixed>>}>
     */
    public function demoBundles(): array
    {
        $bundles = [];
        foreach ((new AlertFactory())->demoSet() as $alert) {
            $measure = (string) $alert['measure_code'];
            $decision = match ($measure) {
                Alert::MEASURE_DECLINE       => OrderEvaluation::DECISION_DECLINE,
                Alert::MEASURE_3DS_CHALLENGE => OrderEvaluation::DECISION_CHALLENGE_3DS,
                default                      => OrderEvaluation::DECISION_PASS,
            };

            $hitsRaw = $alert['hit_details'] ?? null;
            if (is_string($hitsRaw)) {
                $hitsRaw = json_decode($hitsRaw, true) ?: [];
            }
            if (!is_array($hitsRaw)) {
                $hitsRaw = [];
            }

            $evaluatedAt = (string) ($alert['alerted_at'] ?? $alert['created_at']);
            $evaluation = [
                'order_no'         => (string) $alert['order_no'],
                'doopsun_order_id' => null,
                'merchant_id'      => (string) $alert['merchant_id'],
                'risk_level'       => (string) $alert['risk_level'],
                'action'           => (string) ($alert['action_name'] ?? $measure),
                'measure_code'     => $measure,
                'decision'         => $decision,
                'evaluated_at'     => $evaluatedAt,
                'created_at'       => $evaluatedAt,
            ];

            $hits = [];
            foreach ($hitsRaw as $hit) {
                if (!is_array($hit)) {
                    continue;
                }
                $hits[] = [
                    'rule_id'    => (string) ($hit['id'] ?? ''),
                    'rule_name'  => (string) ($hit['name'] ?? ''),
                    'risk_level' => (string) ($hit['risk_level'] ?? $alert['risk_level']),
                    'measure'    => (string) ($hit['measure'] ?? $measure),
                    'created_at' => $evaluatedAt,
                ];
            }
            if ($hits === []) {
                $hits[] = [
                    'rule_id'    => 'R000',
                    'rule_name'  => (string) $alert['rule_name'],
                    'risk_level' => (string) $alert['risk_level'],
                    'measure'    => $measure,
                    'created_at' => $evaluatedAt,
                ];
            }

            $bundles[] = [
                'evaluation' => $evaluation,
                'hits'       => $hits,
            ];
        }

        return $bundles;
    }
}
