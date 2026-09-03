<?php

declare(strict_types=1);

namespace app\validate;

use app\model\Disposition as DispositionModel;
use think\Validate;

/**
 * 风控规则批量配置校验
 */
class Rule extends Validate
{
    protected $rule = [
        'items' => 'require|array|checkRuleItems',
    ];

    protected $message = [
        'items.require' => '请提交规则配置',
        'items.array'   => '规则配置格式无效',
    ];

    protected $scene = [
        'save' => ['items'],
    ];

    /**
     * @param array<string, mixed> $data
     * @return list<array{rule_id: string, config: array<string, mixed>, measure_code: string, enabled: bool}>
     */
    public static function toSaveItems(array $data): array
    {
        $items = $data['items'] ?? [];
        if (!is_array($items)) {
            return [];
        }

        $result = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $statusRaw = $item['enabled'] ?? true;
            $enabled   = filter_var($statusRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($enabled === null) {
                $enabled = in_array($statusRaw, [1, '1', true], true);
            }

            $config = $item['config'] ?? [];
            if (is_string($config)) {
                $decoded = json_decode($config, true);
                $config  = is_array($decoded) ? $decoded : [];
            }
            if (!is_array($config)) {
                $config = [];
            }

            $result[] = [
                'rule_id'      => trim((string) ($item['rule_id'] ?? '')),
                'config'       => $config,
                'measure_code' => trim((string) ($item['measure_code'] ?? '')),
                'enabled'      => (bool) $enabled,
            ];
        }

        return $result;
    }

    protected function checkRuleItems(mixed $value): bool|string
    {
        if (!is_array($value) || $value === []) {
            return '请提交规则配置';
        }

        $enabledCodes = DispositionModel::where('status', 1)->column('code');
        $enabledSet   = array_fill_keys(array_map('strval', $enabledCodes), true);

        foreach ($value as $index => $item) {
            if (!is_array($item)) {
                return '规则配置格式无效';
            }

            $ruleId = trim((string) ($item['rule_id'] ?? ''));
            if ($ruleId === '' || !preg_match('/^R\d{3}$/', $ruleId)) {
                return '规则编号无效（#' . ((int) $index + 1) . '）';
            }

            $measureCode = trim((string) ($item['measure_code'] ?? ''));
            if ($measureCode === '') {
                return '请选择处置策略（' . $ruleId . '）';
            }
            if (!isset($enabledSet[$measureCode])) {
                return '处置策略无效或已停用（' . $ruleId . '）';
            }

            if (array_key_exists('config', $item) && $item['config'] !== null) {
                $config = $item['config'];
                if (is_string($config)) {
                    $decoded = json_decode($config, true);
                    if (!is_array($decoded)) {
                        return '规则阈值格式无效（' . $ruleId . '）';
                    }
                } elseif (!is_array($config)) {
                    return '规则阈值格式无效（' . $ruleId . '）';
                }
            }

            if (array_key_exists('enabled', $item)) {
                $enabled = $item['enabled'];
                if (is_bool($enabled)) {
                    continue;
                }
                if (!in_array($enabled, [0, 1, '0', '1', true, false, 'true', 'false'], true)) {
                    return '启用状态无效（' . $ruleId . '）';
                }
            }
        }

        return true;
    }
}
