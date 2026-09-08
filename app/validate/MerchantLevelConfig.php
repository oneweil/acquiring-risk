<?php

declare(strict_types=1);

namespace app\validate;

use app\model\MerchantLevelPolicy;
use think\Validate;

/**
 * 商户风险等级规则保存校验
 */
class MerchantLevelConfig extends Validate
{
    protected $rule = [
        'low_max'  => 'require|integer|egt:0|elt:100|checkThresholds',
        'mid_max'  => 'require|integer|egt:0|elt:100',
        'policies' => 'require|array|checkPolicies',
    ];

    protected $message = [
        'low_max.require'  => '请填写低风险上限',
        'low_max.integer'  => '低风险上限须为整数',
        'low_max.egt'      => '低风险上限须在 0–100',
        'low_max.elt'      => '低风险上限须在 0–100',
        'mid_max.require'  => '请填写中风险上限',
        'mid_max.integer'  => '中风险上限须为整数',
        'mid_max.egt'      => '中风险上限须在 0–100',
        'mid_max.elt'      => '中风险上限须在 0–100',
        'policies.require' => '请填写等级权益',
        'policies.array'   => '等级权益格式无效',
    ];

    protected $scene = [
        'save' => ['low_max', 'mid_max', 'policies'],
    ];

    /**
     * @param mixed $value
     * @param mixed $rule
     * @param array<string, mixed> $data
     */
    protected function checkThresholds(mixed $value, mixed $rule, array $data = []): bool|string
    {
        $low = (int) $value;
        $mid = (int) ($data['mid_max'] ?? -1);
        if ($low >= $mid) {
            return '低风险上限须小于中风险上限';
        }

        return true;
    }

    /**
     * @param mixed $value
     */
    protected function checkPolicies(mixed $value): bool|string
    {
        if (!is_array($value)) {
            return '等级权益格式无效';
        }

        foreach (MerchantLevelPolicy::LEVELS as $level) {
            if (!isset($value[$level]) || !is_array($value[$level])) {
                return '缺少「' . (MerchantLevelPolicy::LEVEL_LABELS[$level] ?? $level) . '」权益配置';
            }
            $row = $value[$level];
            foreach (['settle_days', 'margin_rate', 'single_limit', 'daily_limit', 'review_cycle'] as $field) {
                if (!array_key_exists($field, $row)) {
                    return '权益字段不完整';
                }
            }

            $settleDays  = $row['settle_days'];
            $marginRate  = $row['margin_rate'];
            $singleLimit = $row['single_limit'];
            $dailyLimit  = $row['daily_limit'];
            $reviewCycle = trim((string) $row['review_cycle']);

            if (!is_numeric($settleDays) || (int) $settleDays < 0) {
                return '结算周期天数无效';
            }
            if (!is_numeric($marginRate) || (int) $marginRate < 0 || (int) $marginRate > 100) {
                return '保证金比例无效';
            }
            if (!is_numeric($singleLimit) || (int) $singleLimit < 0) {
                return '单笔限额无效';
            }
            if (!is_numeric($dailyLimit) || (int) $dailyLimit < 0) {
                return '日累计限额无效';
            }
            if ((int) $dailyLimit < (int) $singleLimit) {
                return '日累计限额不得小于单笔限额';
            }
            if (!in_array($reviewCycle, MerchantLevelPolicy::REVIEW_CYCLES, true)) {
                return '复评频率无效';
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $data
     * @return array{config: array{low_max: int, mid_max: int}, policies: array<string, array{settle_days: int, margin_rate: int, single_limit: int, daily_limit: int, review_cycle: string}>}
     */
    public static function toSaveData(array $data): array
    {
        $policies = [];
        $raw = $data['policies'] ?? [];
        if (!is_array($raw)) {
            $raw = [];
        }

        foreach (MerchantLevelPolicy::LEVELS as $level) {
            $row = is_array($raw[$level] ?? null) ? $raw[$level] : [];
            $policies[$level] = [
                'settle_days'  => (int) ($row['settle_days'] ?? 0),
                'margin_rate'  => (int) ($row['margin_rate'] ?? 0),
                'single_limit' => (int) ($row['single_limit'] ?? 0),
                'daily_limit'  => (int) ($row['daily_limit'] ?? 0),
                'review_cycle' => trim((string) ($row['review_cycle'] ?? '')),
            ];
        }

        return [
            'config' => [
                'low_max' => (int) ($data['low_max'] ?? 0),
                'mid_max' => (int) ($data['mid_max'] ?? 0),
            ],
            'policies' => $policies,
        ];
    }
}
