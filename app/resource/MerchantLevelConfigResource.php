<?php

declare(strict_types=1);

namespace app\resource;

use app\model\MerchantLevelConfig;
use app\model\MerchantLevelPolicy;

/**
 * 商户风险等级规则对外形状（config + policies 一次组装）
 */
class MerchantLevelConfigResource extends JsonResource
{
    /**
     * @param array<string, MerchantLevelPolicy> $policiesByLevel
     */
    public function __construct(mixed $resource, protected array $policiesByLevel = [])
    {
        parent::__construct($resource);
    }

    /**
     * @param array<string, MerchantLevelPolicy> $policiesByLevel
     */
    public static function makeWithPolicies(MerchantLevelConfig $config, array $policiesByLevel): static
    {
        return new static($config, $policiesByLevel);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $lowMax = (int) $this->low_max;
        $midMax = (int) $this->mid_max;

        $policies = [];
        foreach (MerchantLevelPolicy::LEVELS as $level) {
            $policy = $this->policiesByLevel[$level] ?? null;
            if (!$policy instanceof MerchantLevelPolicy) {
                continue;
            }

            $settleDays  = (int) $policy->settle_days;
            $marginRate  = (int) $policy->margin_rate;
            $singleLimit = (int) $policy->single_limit;
            $dailyLimit  = (int) $policy->daily_limit;
            $reviewCycle = (string) $policy->review_cycle;

            $policies[$level] = [
                'level'               => $level,
                'level_label'         => MerchantLevelPolicy::LEVEL_LABELS[$level] ?? $level,
                'settle_days'         => $settleDays,
                'settle_days_label'   => 'T+' . $settleDays,
                'margin_rate'         => $marginRate,
                'margin_rate_label'   => $marginRate . '%',
                'single_limit'        => $singleLimit,
                'single_limit_label'  => number_format($singleLimit) . ' USD',
                'daily_limit'         => $dailyLimit,
                'daily_limit_label'   => number_format($dailyLimit) . ' USD',
                'review_cycle'        => $reviewCycle,
                'review_cycle_label'  => MerchantLevelPolicy::REVIEW_CYCLE_LABELS[$reviewCycle] ?? $reviewCycle,
                'advice'              => MerchantLevelPolicy::LEVEL_ADVICE[$level] ?? '',
            ];
        }

        return [
            'low_max'  => $lowMax,
            'mid_max'  => $midMax,
            'policies' => $policies,
        ];
    }
}
