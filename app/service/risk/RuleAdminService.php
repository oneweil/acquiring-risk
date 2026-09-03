<?php

declare(strict_types=1);

namespace app\service\risk;

use app\model\Disposition as DispositionModel;
use app\model\Rule as RuleModel;
use app\repository\risk\DispositionRepository;
use app\repository\risk\RuleRepository;
use app\resource\RuleResource;
use database\factories\RuleFactory;

/**
 * 后台风控规则配置（分组 list / 批量 save / reset）
 */
class RuleAdminService
{
    /**
     * @return array{groups: list<array<string, mixed>>, measures: list<array<string, mixed>>}
     */
    public function listPayload(): array
    {
        $dispRepo   = new DispositionRepository();
        $ruleRepo   = new RuleRepository();
        $dispByCode = $dispRepo->mapByCode();
        $enabled    = $dispRepo->listEnabledOrdered();

        $measures = [];
        foreach ($enabled as $disp) {
            $risk = (string) $disp->risk_level;
            $measures[] = [
                'code'             => (string) $disp->code,
                'name'             => (string) $disp->name,
                'risk_level'       => $risk,
                'risk_level_label' => DispositionModel::RISK_LEVEL_LABELS[$risk] ?? $risk,
                'priority'         => (int) $disp->priority,
            ];
        }

        $groups = [];
        foreach ($ruleRepo->allGrouped() as $category => $rules) {
            if ($rules === []) {
                continue;
            }

            $ruleRows = [];
            foreach ($rules as $rule) {
                $code = (string) $rule->measure_code;
                $disp = $dispByCode[$code] ?? null;
                $measure = $disp ? [
                    'code'       => (string) $disp->code,
                    'name'       => (string) $disp->name,
                    'risk_level' => (string) $disp->risk_level,
                ] : null;

                $ruleRows[] = RuleResource::makeWithMeasure($rule, $measure)->toArray();
            }

            $groups[] = [
                'category'       => $category,
                'category_label' => RuleModel::CATEGORY_LABELS[$category] ?? $category,
                'rules'          => $ruleRows,
            ];
        }

        return [
            'groups'   => $groups,
            'measures' => $measures,
        ];
    }

    /**
     * @param list<array{rule_id: string, config: array<string, mixed>, measure_code: string, enabled: bool}> $items
     * @return array{groups: list<array<string, mixed>>, measures: list<array<string, mixed>>}
     */
    public function batchSave(array $items): array
    {
        (new RuleRepository())->batchUpdate($items);

        return $this->listPayload();
    }

    /**
     * @return array{groups: list<array<string, mixed>>, measures: list<array<string, mixed>>}
     */
    public function resetToDefaults(): array
    {
        (new RuleRepository())->resetToDefaults((new RuleFactory())->builtinRows());

        return $this->listPayload();
    }
}
