<?php

declare(strict_types=1);

namespace app\validate;

use app\model\Disposition;
use app\model\Rule;
use think\Validate;

/**
 * STR 推送规则页参数校验
 */
class StrPushRule extends Validate
{
    protected $rule = [
        'category'           => 'checkCategory',
        'push_str'           => 'checkPushStrFilter',
        'keyword'            => 'max:128',
        'page'               => 'integer|gt:0',
        'pageSize'           => 'integer|in:10,20,50',
        'enabled'            => 'require|checkBool',
        'push_by_risk_level' => 'require|checkBool',
        'risk_levels'        => 'require|array|checkRiskLevels',
        'push_ltr'           => 'require|checkBool',
        'ltr_threshold_usd'  => 'require|integer|gt:0',
        'ltr_threshold_hkd'  => 'require|integer|gt:0',
        'items'              => 'require|array|checkPushItems',
    ];

    protected $message = [
        'keyword.max'                  => '关键词最长 128 字符',
        'page.integer'                 => '页码无效',
        'page.gt'                      => '页码无效',
        'pageSize.integer'             => '每页条数无效',
        'pageSize.in'                  => '每页条数无效',
        'enabled.require'              => '请选择 STR 自动推送总开关',
        'push_by_risk_level.require'   => '请选择按风险等级推送开关',
        'risk_levels.require'          => '请选择触发 STR 的风险等级',
        'risk_levels.array'            => '风险等级格式无效',
        'push_ltr.require'             => '请选择大额 LTR 自动推送开关',
        'ltr_threshold_usd.require'    => '请填写 LTR 阈值（USD）',
        'ltr_threshold_usd.integer'    => 'LTR 阈值（USD）须为正整数',
        'ltr_threshold_usd.gt'         => 'LTR 阈值（USD）须为正整数',
        'ltr_threshold_hkd.require'    => '请填写 LTR 阈值（HKD）',
        'ltr_threshold_hkd.integer'    => 'LTR 阈值（HKD）须为正整数',
        'ltr_threshold_hkd.gt'         => 'LTR 阈值（HKD）须为正整数',
        'items.require'                => '请提交规则推送开关',
        'items.array'                  => '规则推送开关格式无效',
    ];

    public function sceneList()
    {
        return $this->only(['category', 'push_str', 'keyword', 'page', 'pageSize']);
    }

    public function sceneSave()
    {
        return $this->only([
            'enabled',
            'push_by_risk_level',
            'risk_levels',
            'push_ltr',
            'ltr_threshold_usd',
            'ltr_threshold_hkd',
            'items',
        ]);
    }

    /**
     * @param mixed $value
     */
    protected function checkCategory(mixed $value): bool|string
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (!in_array((string) $value, Rule::CATEGORIES, true)) {
            return '规则类别无效';
        }

        return true;
    }

    /**
     * @param mixed $value
     */
    protected function checkPushStrFilter(mixed $value): bool|string
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (!in_array((string) $value, ['0', '1'], true) && !is_bool($value)) {
            return '推送状态筛选无效';
        }

        return true;
    }

    /**
     * @param mixed $value
     */
    protected function checkBool(mixed $value): bool|string
    {
        if (is_bool($value)) {
            return true;
        }
        if (in_array($value, [0, 1, '0', '1', true, false], true)) {
            return true;
        }

        return '开关值无效';
    }

    /**
     * @param mixed $value
     */
    protected function checkRiskLevels(mixed $value): bool|string
    {
        if (!is_array($value) || $value === []) {
            return '请至少选择一个触发 STR 的风险等级';
        }
        foreach ($value as $level) {
            if (!in_array((string) $level, Disposition::RISK_LEVELS, true)) {
                return '风险等级无效';
            }
        }

        return true;
    }

    /**
     * @param mixed $value
     */
    protected function checkPushItems(mixed $value): bool|string
    {
        if (!is_array($value) || $value === []) {
            return '请提交规则推送开关';
        }
        foreach ($value as $row) {
            if (!is_array($row)) {
                return '规则推送开关格式无效';
            }
            $ruleId = trim((string) ($row['rule_id'] ?? ''));
            if ($ruleId === '') {
                return '规则编号不能为空';
            }
            if (!array_key_exists('push_str', $row)) {
                return '缺少推送开关：' . $ruleId;
            }
            $ok = $this->checkBool($row['push_str']);
            if ($ok !== true) {
                return '规则推送开关无效：' . $ruleId;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $data
     * @return array{category?: string, push_str?: int, keyword?: string}
     */
    public static function toListFilters(array $data): array
    {
        $filters = [];
        if (isset($data['category']) && trim((string) $data['category']) !== '') {
            $filters['category'] = trim((string) $data['category']);
        }
        if (isset($data['push_str']) && $data['push_str'] !== '' && $data['push_str'] !== null) {
            $filters['push_str'] = (int) ((string) $data['push_str'] === '1' || $data['push_str'] === true || $data['push_str'] === 1);
        }
        if (isset($data['keyword']) && trim((string) $data['keyword']) !== '') {
            $filters['keyword'] = trim((string) $data['keyword']);
        }

        return $filters;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function toListPageSize(array $data): int
    {
        $size = (int) ($data['pageSize'] ?? 0);
        if (in_array($size, [10, 20, 50], true)) {
            return $size;
        }

        return (int) config('paginate.list_rows', 10);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{
     *   config: array{
     *     enabled: bool,
     *     push_by_risk_level: bool,
     *     risk_levels: list<string>,
     *     push_ltr: bool,
     *     ltr_threshold_usd: int,
     *     ltr_threshold_hkd: int
     *   },
     *   items: list<array{rule_id: string, push_str: bool}>
     * }
     */
    public static function toSaveData(array $data): array
    {
        $levels = [];
        $rawLevels = $data['risk_levels'] ?? [];
        if (is_array($rawLevels)) {
            foreach ($rawLevels as $level) {
                $level = trim((string) $level);
                if ($level !== '' && !in_array($level, $levels, true)) {
                    $levels[] = $level;
                }
            }
        }

        $items = [];
        $rawItems = $data['items'] ?? [];
        if (is_array($rawItems)) {
            foreach ($rawItems as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $ruleId = trim((string) ($row['rule_id'] ?? ''));
                if ($ruleId === '') {
                    continue;
                }
                $items[] = [
                    'rule_id'  => $ruleId,
                    'push_str' => self::toBool($row['push_str'] ?? false),
                ];
            }
        }

        return [
            'config' => [
                'enabled'            => self::toBool($data['enabled'] ?? false),
                'push_by_risk_level' => self::toBool($data['push_by_risk_level'] ?? false),
                'risk_levels'        => $levels,
                'push_ltr'           => self::toBool($data['push_ltr'] ?? false),
                'ltr_threshold_usd'  => (int) ($data['ltr_threshold_usd'] ?? 0),
                'ltr_threshold_hkd'  => (int) ($data['ltr_threshold_hkd'] ?? 0),
            ],
            'items' => $items,
        ];
    }

    private static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($parsed !== null) {
            return $parsed;
        }

        return in_array($value, [1, '1'], true);
    }
}
