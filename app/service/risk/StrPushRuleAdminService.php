<?php

declare(strict_types=1);

namespace app\service\risk;

use app\model\Disposition as DispositionModel;
use app\model\StrPushConfig;
use app\repository\risk\DispositionRepository;
use app\repository\risk\RuleRepository;
use app\repository\risk\StrPushConfigRepository;
use app\resource\StrPushConfigResource;
use app\resource\StrPushRuleResource;
use database\factories\RuleFactory;
use database\factories\StrPushConfigFactory;
use think\facade\Db;

/**
 * 后台 STR 推送规则配置
 */
class StrPushRuleAdminService
{
    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $config = $this->ensureConfig();
        $ruleRepo = new RuleRepository();

        return StrPushConfigResource::makeWithCounts($config, [
            'enabled_count' => $ruleRepo->countPushStrEnabled(),
            'total_count'   => $ruleRepo->countAll(),
        ]);
    }

    /**
     * @param array{category?: string, push_str?: int|bool, keyword?: string} $filters
     * @return array<string, mixed>
     */
    public function listRules(array $filters, int $page, int $pageSize): array
    {
        $paginator = (new RuleRepository())->searchForStrPush($filters, $page, $pageSize);
        $dispByCode = (new DispositionRepository())->mapByCode();

        $measureByRuleId = [];
        foreach ($paginator as $rule) {
            $code = (string) $rule->measure_code;
            $disp = $dispByCode[$code] ?? null;
            $measureByRuleId[(string) $rule->rule_id] = $disp ? [
                'code'       => (string) $disp->code,
                'name'       => (string) $disp->name,
                'risk_level' => (string) $disp->risk_level,
            ] : null;
        }

        $payload         = $paginator->toArray();
        $payload['data'] = StrPushRuleResource::collectionWithMeasures($paginator, $measureByRuleId);

        return $payload;
    }

    /**
     * @param array{
     *   config: array{
     *     enabled: bool,
     *     push_by_risk_level: bool,
     *     risk_levels: list<string>,
     *     push_ltr: bool,
     *     ltr_threshold_usd: int,
     *     ltr_threshold_hkd: int
     *   },
     *   items: list<array{rule_id: string, push_str: bool}>
     * } $data
     * @return array<string, mixed>
     */
    public function save(array $data): array
    {
        Db::transaction(function () use ($data): void {
            (new StrPushConfigRepository())->saveOrCreate($data['config']);
            (new RuleRepository())->batchUpdatePushStr($data['items']);
        });

        return $this->getConfig();
    }

    /**
     * @return array<string, mixed>
     */
    public function resetToDefaults(): array
    {
        Db::transaction(function (): void {
            $factory = new StrPushConfigFactory();
            $builtin = $factory->builtinConfig();
            (new StrPushConfigRepository())->saveOrCreate([
                'enabled'            => (bool) $builtin['enabled'],
                'push_by_risk_level' => (bool) $builtin['push_by_risk_level'],
                'risk_levels'        => StrPushConfig::DEFAULT_RISK_LEVELS,
                'push_ltr'           => (bool) $builtin['push_ltr'],
                'ltr_threshold_usd'  => (int) $builtin['ltr_threshold_usd'],
                'ltr_threshold_hkd'  => (int) $builtin['ltr_threshold_hkd'],
            ]);

            $items = [];
            foreach ((new RuleFactory())->builtinRows() as $row) {
                $items[] = [
                    'rule_id'  => (string) $row['rule_id'],
                    'push_str' => !empty($row['push_str']),
                ];
            }
            (new RuleRepository())->batchUpdatePushStr($items);
        });

        return $this->getConfig();
    }

    /**
     * @return array<string, string>
     */
    public function categoryOptions(): array
    {
        return \app\model\Rule::CATEGORY_LABELS;
    }

    /**
     * @return array<string, string>
     */
    public function riskLevelOptions(): array
    {
        return DispositionModel::RISK_LEVEL_LABELS;
    }

    private function ensureConfig(): StrPushConfig
    {
        $repo = new StrPushConfigRepository();
        $config = $repo->get();
        if ($config !== null) {
            return $config;
        }

        $builtin = (new StrPushConfigFactory())->builtinConfig();

        return $repo->saveOrCreate([
            'enabled'            => (bool) $builtin['enabled'],
            'push_by_risk_level' => (bool) $builtin['push_by_risk_level'],
            'risk_levels'        => StrPushConfig::DEFAULT_RISK_LEVELS,
            'push_ltr'           => (bool) $builtin['push_ltr'],
            'ltr_threshold_usd'  => (int) $builtin['ltr_threshold_usd'],
            'ltr_threshold_hkd'  => (int) $builtin['ltr_threshold_hkd'],
        ]);
    }
}
