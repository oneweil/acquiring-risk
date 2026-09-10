<?php

declare(strict_types=1);

namespace database\factories;

use app\model\StrPushConfig;

/**
 * STR 推送全局配置默认数据
 */
class StrPushConfigFactory
{
    /**
     * @return array<string, mixed>
     */
    public function builtinConfig(): array
    {
        $now = date('Y-m-d H:i:s');

        return [
            'id'                 => StrPushConfig::SINGLETON_ID,
            'enabled'            => 1,
            'push_by_risk_level' => 1,
            'risk_levels'        => json_encode(
                StrPushConfig::DEFAULT_RISK_LEVELS,
                JSON_UNESCAPED_UNICODE
            ),
            'push_ltr'           => 1,
            'ltr_threshold_usd'  => StrPushConfig::DEFAULT_LTR_USD,
            'ltr_threshold_hkd'  => StrPushConfig::DEFAULT_LTR_HKD,
            'created_at'         => $now,
            'updated_at'         => $now,
        ];
    }

    /**
     * 是否默认开启某规则的 STR 推送（与 docs/05-rules-catalog 一致）
     */
    public static function defaultPushStr(string $category, string $measureCode, ?string $riskLevel = null): bool
    {
        if (in_array($category, StrPushConfig::DEFAULT_PUSH_CATEGORIES, true)) {
            return true;
        }

        if ($riskLevel !== null && $riskLevel !== '') {
            return in_array($riskLevel, ['high', 'critical'], true);
        }

        // measure_code → 内置处置风险等级（seed 阶段可能尚未联表）
        $map = [
            'DECLINE'            => 'critical',
            'SUSPEND_MERCHANT'   => 'critical',
            '3DS_CHALLENGE'      => 'high',
            'DELAY_SETTLE'       => 'mid',
            'LIMIT_AMOUNT'       => 'high',
            'WATCHLIST'          => 'mid',
            'CHARGEBACK_INQUIRY' => 'high',
            'ALERT_ONLY'         => 'mid',
        ];
        $level = $map[$measureCode] ?? '';

        return in_array($level, ['high', 'critical'], true);
    }
}
