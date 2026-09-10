<?php

declare(strict_types=1);

namespace app\service\risk;

use app\model\StrPushConfig;
use app\repository\risk\RuleRepository;
use app\repository\risk\StrPushConfigRepository;

/**
 * STR/LTR 自动推送判定（供后续 evaluate / 报送模块调用）
 */
class StrPushMatcher
{
    /**
     * 是否应自动推送可疑 STR
     *
     * @param list<string> $hitRuleIds 评估命中的规则编号
     * @param string       $riskLevel  订单综合风险等级英文枚举
     */
    public function shouldPushStr(array $hitRuleIds, string $riskLevel): bool
    {
        $config = (new StrPushConfigRepository())->get();
        if ($config === null || !(bool) $config->enabled) {
            return false;
        }

        if ($hitRuleIds === []) {
            return false;
        }

        if ((bool) $config->push_by_risk_level) {
            $levels = $this->normalizeRiskLevels($config->risk_levels);
            if ($riskLevel !== '' && in_array($riskLevel, $levels, true)) {
                return true;
            }
        }

        $pushMap = (new RuleRepository())->mapPushStrByRuleIds($hitRuleIds);
        foreach ($hitRuleIds as $ruleId) {
            if (!empty($pushMap[$ruleId])) {
                return true;
            }
        }

        return false;
    }

    /**
     * 是否应自动推送大额 LTR（需全局 push_ltr 开启）
     */
    public function shouldPushLtr(float|int|string $amount, string $currency): bool
    {
        $config = (new StrPushConfigRepository())->get();
        if ($config === null || !(bool) $config->enabled || !(bool) $config->push_ltr) {
            return false;
        }

        $amount = (float) $amount;
        $currency = strtoupper(trim($currency));
        $usd = (int) $config->ltr_threshold_usd;
        $hkd = (int) $config->ltr_threshold_hkd;

        if ($currency === 'HKD') {
            return $amount >= $hkd;
        }
        if ($currency === 'USD') {
            return $amount >= $usd;
        }

        // 其它币种按 USD 阈值兜底（与原型一致）
        return $amount >= $usd;
    }

    /**
     * @param mixed $levels
     * @return list<string>
     */
    private function normalizeRiskLevels(mixed $levels): array
    {
        if (is_string($levels)) {
            $decoded = json_decode($levels, true);
            $levels  = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($levels) || $levels === []) {
            return StrPushConfig::DEFAULT_RISK_LEVELS;
        }

        return array_values(array_map('strval', $levels));
    }
}
