<?php

declare(strict_types=1);

namespace app\validate;

use app\model\MerchantAssessDimension;
use database\factories\MerchantAssessRuleFactory;
use think\Validate;

/**
 * 商户评估规则保存校验
 */
class MerchantAssessConfig extends Validate
{
    protected $rule = [
        'dimensions' => 'require|array|checkDimensions',
        'rules'      => 'require|array|checkRules',
    ];

    protected $message = [
        'dimensions.require' => '请填写维度权重',
        'dimensions.array'   => '维度权重格式无效',
        'rules.require'      => '请填写评估规则',
        'rules.array'        => '评估规则格式无效',
    ];

    protected $scene = [
        'save' => ['dimensions', 'rules'],
    ];

    /**
     * @param mixed $value
     */
    protected function checkDimensions(mixed $value): bool|string
    {
        if (!is_array($value)) {
            return '维度权重格式无效';
        }

        $byKey = [];
        foreach ($value as $row) {
            if (!is_array($row)) {
                return '维度权重格式无效';
            }
            $dimKey = trim((string) ($row['dim_key'] ?? ''));
            if ($dimKey === '' || !in_array($dimKey, MerchantAssessDimension::DIM_KEYS, true)) {
                return '存在无效维度 key';
            }
            if (isset($byKey[$dimKey])) {
                return '维度 key 重复：' . $dimKey;
            }

            if (!array_key_exists('weight', $row) || !array_key_exists('onboarding_weight', $row)) {
                return '维度权重字段不完整';
            }
            if (!is_numeric($row['weight']) || (int) $row['weight'] < 0 || (int) $row['weight'] > 100) {
                return '复评权重须为 0–100 的整数';
            }
            if (!is_numeric($row['onboarding_weight']) || (int) $row['onboarding_weight'] < 0 || (int) $row['onboarding_weight'] > 100) {
                return '入网权重须为 0–100 的整数';
            }

            $byKey[$dimKey] = [
                'weight'            => (int) $row['weight'],
                'onboarding_weight' => (int) $row['onboarding_weight'],
            ];
        }

        foreach (MerchantAssessDimension::DIM_KEYS as $dimKey) {
            if (!isset($byKey[$dimKey])) {
                $label = MerchantAssessDimension::DIM_LABELS[$dimKey] ?? $dimKey;

                return '缺少维度「' . $label . '」';
            }
        }

        $weightSum = 0;
        $onboardSum = 0;
        foreach ($byKey as $dimKey => $row) {
            $weightSum += $row['weight'];
            $onboardSum += $row['onboarding_weight'];
            if (in_array($dimKey, MerchantAssessDimension::ONBOARDING_EXCLUDED_DIMS, true)
                && $row['onboarding_weight'] !== 0) {
                $label = MerchantAssessDimension::DIM_LABELS[$dimKey] ?? $dimKey;

                return '入网排除维度「' . $label . '」权重须为 0';
            }
        }

        if ($weightSum !== 100) {
            return '复评维度权重合计须为 100%（当前 ' . $weightSum . '%）';
        }
        if ($onboardSum !== 100) {
            return '入网维度权重合计须为 100%（当前 ' . $onboardSum . '%）';
        }

        return true;
    }

    /**
     * @param mixed $value
     */
    protected function checkRules(mixed $value): bool|string
    {
        if (!is_array($value)) {
            return '评估规则格式无效';
        }

        $expectedIds = [];
        foreach ((new MerchantAssessRuleFactory())->definitions() as $def) {
            $expectedIds[$def['rule_id']] = $def;
        }

        $seen = [];
        $geoCountries = [];

        foreach ($value as $row) {
            if (!is_array($row)) {
                return '评估规则格式无效';
            }
            $ruleId = trim((string) ($row['rule_id'] ?? ''));
            if ($ruleId === '' || !isset($expectedIds[$ruleId])) {
                return '存在未知规则编号：' . ($ruleId !== '' ? $ruleId : '空');
            }
            if (isset($seen[$ruleId])) {
                return '规则编号重复：' . $ruleId;
            }
            $seen[$ruleId] = true;

            $config = $row['config'] ?? null;
            if (!is_array($config)) {
                return '规则 ' . $ruleId . ' 的 config 无效';
            }

            if (!array_key_exists('score', $row) || !is_numeric($row['score'])) {
                return '规则 ' . $ruleId . ' 缺少有效 score';
            }
            $score = (int) $row['score'];
            if ($score < 0 || $score > 100) {
                return '规则 ' . $ruleId . ' 的 score 须在 0–100';
            }

            $category = $expectedIds[$ruleId]['category'];
            $check = $this->validateRuleConfig($category, $config, $ruleId);
            if ($check !== true) {
                return $check;
            }

            if ($category === 'geo') {
                $countries = $config['countries'] ?? [];
                if (!is_array($countries)) {
                    return '规则 ' . $ruleId . ' 的 countries 无效';
                }
                foreach ($countries as $cc) {
                    $cc = strtoupper(trim((string) $cc));
                    if ($cc === '') {
                        continue;
                    }
                    if (isset($geoCountries[$cc])) {
                        return '注册国家「' . $cc . '」不可归属多个地理档';
                    }
                    $geoCountries[$cc] = $ruleId;
                }
            }
        }

        foreach (array_keys($expectedIds) as $ruleId) {
            if (!isset($seen[$ruleId])) {
                return '缺少规则：' . $ruleId;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function validateRuleConfig(string $category, array $config, string $ruleId): bool|string
    {
        if (in_array($category, ['industry', 'website', 'compliance'], true)) {
            if (!isset($config['match_key']) || trim((string) $config['match_key']) === '') {
                return '规则 ' . $ruleId . ' 缺少 match_key';
            }

            return true;
        }

        if (in_array($category, ['chargeback', 'fraud', 'refund', 'tenure'], true)) {
            if (!isset($config['threshold']) || !is_numeric($config['threshold'])) {
                return '规则 ' . $ruleId . ' 缺少有效 threshold';
            }
            if ((float) $config['threshold'] < 0) {
                return '规则 ' . $ruleId . ' 的 threshold 不得为负';
            }

            return true;
        }

        if ($category === 'volume_anomaly') {
            return $this->validateVolumeConfig($config, $ruleId);
        }

        if ($category === 'geo') {
            $hasCountries = isset($config['countries']) && is_array($config['countries']) && $config['countries'] !== [];
            $hasMatchKey  = isset($config['match_key']) && trim((string) $config['match_key']) !== '';
            if (!$hasCountries && !$hasMatchKey) {
                return '规则 ' . $ruleId . ' 须配置 countries 或 match_key';
            }

            return true;
        }

        return '规则 ' . $ruleId . ' 分类无效';
    }

    /**
     * @param array<string, mixed> $config
     */
    private function validateVolumeConfig(array $config, string $ruleId): bool|string
    {
        $needLow = in_array($ruleId, ['MA_VOL_0', 'MA_VOL_1'], true);
        $needHigh = in_array($ruleId, ['MA_VOL_1', 'MA_VOL_2'], true);

        if ($needLow) {
            if (!isset($config['low_threshold']) || !is_numeric($config['low_threshold'])) {
                return '规则 ' . $ruleId . ' 缺少有效 low_threshold';
            }
            if ((float) $config['low_threshold'] < 0) {
                return '规则 ' . $ruleId . ' 的 low_threshold 不得为负';
            }
        }

        if ($needHigh) {
            if (!isset($config['threshold']) || !is_numeric($config['threshold'])) {
                return '规则 ' . $ruleId . ' 缺少有效 threshold';
            }
            if ((float) $config['threshold'] < 0) {
                return '规则 ' . $ruleId . ' 的 threshold 不得为负';
            }
        }

        if ($needLow && $needHigh
            && (float) $config['threshold'] <= (float) $config['low_threshold']) {
            return '规则 ' . $ruleId . ' 的 threshold 须大于 low_threshold';
        }

        return true;
    }

    /**
     * @param array<string, mixed> $data
     * @return array{
     *   dimensions: array<string, array{weight: int, onboarding_weight: int}>,
     *   rules: list<array{rule_id: string, score: int, config: array<string, mixed>, enabled: bool}>
     * }
     */
    public static function toSaveData(array $data): array
    {
        $dimensions = [];
        $rawDims = is_array($data['dimensions'] ?? null) ? $data['dimensions'] : [];
        foreach ($rawDims as $row) {
            if (!is_array($row)) {
                continue;
            }
            $dimKey = trim((string) ($row['dim_key'] ?? ''));
            if ($dimKey === '') {
                continue;
            }
            $dimensions[$dimKey] = [
                'weight'            => (int) ($row['weight'] ?? 0),
                'onboarding_weight' => (int) ($row['onboarding_weight'] ?? 0),
            ];
        }

        $rules = [];
        $rawRules = is_array($data['rules'] ?? null) ? $data['rules'] : [];
        foreach ($rawRules as $row) {
            if (!is_array($row)) {
                continue;
            }
            $ruleId = trim((string) ($row['rule_id'] ?? ''));
            if ($ruleId === '') {
                continue;
            }
            $config = is_array($row['config'] ?? null) ? $row['config'] : [];
            $rules[] = [
                'rule_id' => $ruleId,
                'score'   => (int) ($row['score'] ?? 0),
                'config'  => self::normalizeConfig($config),
                'enabled' => !empty($row['enabled']),
            ];
        }

        return [
            'dimensions' => $dimensions,
            'rules'      => $rules,
        ];
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private static function normalizeConfig(array $config): array
    {
        $out = [];
        if (isset($config['match_key'])) {
            $out['match_key'] = trim((string) $config['match_key']);
        }
        if (isset($config['low_threshold']) && is_numeric($config['low_threshold'])) {
            $low = (float) $config['low_threshold'];
            $out['low_threshold'] = abs($low - (int) $low) < 0.00001
                ? (int) $low
                : $low;
        }
        if (isset($config['threshold']) && is_numeric($config['threshold'])) {
            $threshold = (float) $config['threshold'];
            $out['threshold'] = abs($threshold - (int) $threshold) < 0.00001
                ? (int) $threshold
                : $threshold;
        }
        if (isset($config['countries']) && is_array($config['countries'])) {
            $countries = [];
            foreach ($config['countries'] as $cc) {
                $cc = strtoupper(trim((string) $cc));
                if ($cc !== '') {
                    $countries[] = $cc;
                }
            }
            $out['countries'] = array_values(array_unique($countries));
        }

        return $out;
    }
}
