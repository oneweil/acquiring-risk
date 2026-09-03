<?php

declare(strict_types=1);

namespace app\resource;

use app\model\Disposition;
use app\model\Rule;

/**
 * 风控规则对外形状（风险等级由 disposition 组装，非表字段）
 */
class RuleResource extends JsonResource
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
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $category    = (string) $this->category;
        $measureCode = (string) $this->measure_code;
        $config      = $this->config;
        if (is_string($config)) {
            $decoded = json_decode($config, true);
            $config  = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($config)) {
            $config = [];
        }

        $riskLevel = (string) ($this->measure['risk_level'] ?? '');
        $measureName = (string) ($this->measure['name'] ?? '');

        return [
            'id'               => (int) $this->id,
            'rule_id'          => (string) $this->rule_id,
            'category'         => $category,
            'category_label'   => Rule::CATEGORY_LABELS[$category] ?? $category,
            'name'             => (string) $this->name,
            'description'      => (string) $this->description,
            'content_template' => (string) $this->content_template,
            'config'           => $config,
            'measure_code'     => $measureCode,
            'measure_name'     => $measureName,
            'risk_level'       => $riskLevel,
            'risk_level_label' => $riskLevel !== ''
                ? (Disposition::RISK_LEVEL_LABELS[$riskLevel] ?? $riskLevel)
                : '',
            'enabled'          => (bool) $this->enabled,
            'sort'             => (int) $this->sort,
        ];
    }
}
