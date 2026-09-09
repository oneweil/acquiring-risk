<?php

declare(strict_types=1);

namespace app\resource;

use app\model\Disposition;
use app\model\Rule;

/**
 * STR 推送规则列表行（规则 + 处置策略展示字段 + push_str）
 */
class StrPushRuleResource extends JsonResource
{
    /**
     * @param array{code?: string, name?: string, risk_level?: string}|null $measure
     */
    public function __construct(mixed $resource, protected ?array $measure = null)
    {
        parent::__construct($resource);
    }

    /**
     * @param array{code?: string, name?: string, risk_level?: string}|null $measure
     */
    public static function makeWithMeasure(mixed $resource, ?array $measure): static
    {
        return new static($resource, $measure);
    }

    /**
     * @param iterable<mixed> $items
     * @param array<string, array{code?: string, name?: string, risk_level?: string}|null> $measureByRuleId
     * @return list<array<string, mixed>>
     */
    public static function collectionWithMeasures(iterable $items, array $measureByRuleId): array
    {
        $result = [];
        foreach ($items as $item) {
            $ruleId = is_object($item) ? (string) ($item->rule_id ?? '') : (string) ($item['rule_id'] ?? '');
            $result[] = (new static($item, $measureByRuleId[$ruleId] ?? null))->toArray();
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $category  = (string) $this->category;
        $riskLevel = (string) ($this->measure['risk_level'] ?? '');

        return [
            'rule_id'          => (string) $this->rule_id,
            'name'             => (string) $this->name,
            'category'         => $category,
            'category_label'   => Rule::CATEGORY_LABELS[$category] ?? $category,
            'description'      => (string) $this->description,
            'measure_code'     => (string) $this->measure_code,
            'measure_name'     => (string) ($this->measure['name'] ?? ''),
            'risk_level'       => $riskLevel,
            'risk_level_label' => $riskLevel !== ''
                ? (Disposition::RISK_LEVEL_LABELS[$riskLevel] ?? $riskLevel)
                : '',
            'push_str'         => (bool) $this->push_str,
        ];
    }
}
