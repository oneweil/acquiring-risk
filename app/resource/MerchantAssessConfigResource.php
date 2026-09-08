<?php

declare(strict_types=1);

namespace app\resource;

use app\model\MerchantAssessDimension;
use app\model\MerchantAssessRule;

/**
 * 商户评估规则配置对外形状（dimensions + rules）
 */
class MerchantAssessConfigResource extends JsonResource
{
    /**
     * @param list<MerchantAssessDimension> $dimensions
     * @param list<MerchantAssessRule>      $rules
     */
    public function __construct(
        protected array $dimensions,
        protected array $rules,
    ) {
        parent::__construct(null);
    }

    /**
     * @param list<MerchantAssessDimension> $dimensions
     * @param list<MerchantAssessRule>      $rules
     */
    public static function makeFrom(array $dimensions, array $rules): static
    {
        return new static($dimensions, $rules);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $dimensions = [];
        foreach ($this->dimensions as $dim) {
            $dimKey = (string) $dim->dim_key;
            $dimensions[] = [
                'dim_key'            => $dimKey,
                'name'               => (string) $dim->name,
                'weight'             => (int) $dim->weight,
                'onboarding_weight'  => (int) $dim->onboarding_weight,
                'sort'               => (int) $dim->sort,
                'onboarding_excluded'=> in_array($dimKey, MerchantAssessDimension::ONBOARDING_EXCLUDED_DIMS, true),
            ];
        }

        $rules = [];
        foreach ($this->rules as $rule) {
            $category = (string) $rule->category;
            $config   = $rule->config;
            if (!is_array($config)) {
                $config = [];
            }
            unset($config['score']);

            $rules[] = [
                'rule_id'          => (string) $rule->rule_id,
                'category'         => $category,
                'category_label'   => MerchantAssessRule::CATEGORY_LABELS[$category] ?? $category,
                'description'      => (string) $rule->description,
                'content_template' => (string) $rule->content_template,
                'score'            => (int) $rule->score,
                'config'           => $config,
                'enabled'          => (bool) $rule->enabled,
                'sort'             => (int) $rule->sort,
                'periodic_only'    => in_array($category, MerchantAssessRule::PERIODIC_ONLY_CATEGORIES, true),
            ];
        }

        $onboarding = [];
        foreach ($dimensions as $dim) {
            if (!empty($dim['onboarding_excluded'])) {
                continue;
            }
            $onboarding[] = [
                'dim_key' => $dim['dim_key'],
                'name'    => $dim['name'],
                'weight'  => $dim['onboarding_weight'],
            ];
        }

        return [
            'dimensions'            => $dimensions,
            'rules'                 => $rules,
            'onboarding_dimensions' => $onboarding,
            'category_labels'       => MerchantAssessRule::CATEGORY_LABELS,
        ];
    }
}
