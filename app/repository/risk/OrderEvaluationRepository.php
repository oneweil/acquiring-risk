<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\OrderEvaluation;
use app\model\OrderHit;
use think\db\exception\DbException;

class OrderEvaluationRepository
{
    /**
     * @param list<string> $orderNos
     * @return array<string, array{evaluation: OrderEvaluation, hit_rule: string, hits: list<array<string, mixed>>}>
     *
     * @throws DbException
     */
    public function mapByOrderNos(array $orderNos, bool $withHits = false): array
    {
        $nos = [];
        foreach ($orderNos as $no) {
            $no = trim((string) $no);
            if ($no !== '') {
                $nos[] = $no;
            }
        }
        $nos = array_values(array_unique($nos));
        if ($nos === []) {
            return [];
        }

        $evals = OrderEvaluation::whereIn('order_no', $nos)->select();
        if ($evals->isEmpty()) {
            return [];
        }

        /** @var array<int, OrderEvaluation> $byId */
        $byId = [];
        $evalIds = [];
        foreach ($evals as $eval) {
            $eid = (int) $eval->id;
            $evalIds[] = $eid;
            $byId[$eid] = $eval;
        }

        /** @var array<int, list<array<string, mixed>>> $hitsByEval */
        $hitsByEval = [];
        if ($evalIds !== []) {
            $hitRows = OrderHit::whereIn('evaluation_id', $evalIds)
                ->order('id', 'asc')
                ->select();
            foreach ($hitRows as $hit) {
                $eid = (int) $hit->evaluation_id;
                $hitsByEval[$eid][] = [
                    'rule_id'    => (string) $hit->rule_id,
                    'rule_name'  => (string) $hit->rule_name,
                    'risk_level' => $hit->risk_level !== null ? (string) $hit->risk_level : null,
                    'measure'    => $hit->measure !== null ? (string) $hit->measure : null,
                ];
            }
        }

        $map = [];
        foreach ($byId as $eid => $eval) {
            $hits = $hitsByEval[$eid] ?? [];
            $ruleNames = [];
            foreach ($hits as $h) {
                $rn = trim((string) ($h['rule_name'] ?? ''));
                if ($rn !== '' && !in_array($rn, $ruleNames, true)) {
                    $ruleNames[] = $rn;
                }
            }
            $map[(string) $eval->order_no] = [
                'evaluation' => $eval,
                'hit_rule'   => $ruleNames !== [] ? implode('、', $ruleNames) : '',
                'hits'       => $withHits ? $hits : [],
            ];
        }

        return $map;
    }

    /**
     * @return list<string>
     *
     * @throws DbException
     */
    public function orderNosByRiskLevel(string $riskLevel): array
    {
        if ($riskLevel === '') {
            return [];
        }

        return array_values(array_map('strval', OrderEvaluation::where('risk_level', $riskLevel)->column('order_no')));
    }

    /**
     * @return list<string>
     *
     * @throws DbException
     */
    public function orderNosByDecision(string $decision): array
    {
        if ($decision === '') {
            return [];
        }

        return array_values(array_map('strval', OrderEvaluation::where('decision', $decision)->column('order_no')));
    }
}
