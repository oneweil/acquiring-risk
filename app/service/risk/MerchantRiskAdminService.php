<?php

declare(strict_types=1);

namespace app\service\risk;

use app\model\MerchantAssessDimension;
use app\repository\risk\MerchantAssessDimensionRepository;
use app\repository\risk\MerchantAssessRuleRepository;
use app\resource\MerchantAssessConfigResource;
use database\factories\MerchantAssessDimensionFactory;
use database\factories\MerchantAssessRuleFactory;
use think\facade\Db;

/**
 * 后台商户评估规则（维度权重 + 规则行）
 */
class MerchantRiskAdminService
{
    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $dimRepo  = new MerchantAssessDimensionRepository();
        $ruleRepo = new MerchantAssessRuleRepository();

        $dimensions = $dimRepo->allOrdered();
        if (count($dimensions) < count(MerchantAssessDimension::DIM_KEYS)) {
            $this->seedDefaults();
            $dimensions = $dimRepo->allOrdered();
        }

        $rules = $ruleRepo->allOrdered();
        if ($rules === []) {
            $this->seedDefaults();
            $dimensions = $dimRepo->allOrdered();
            $rules      = $ruleRepo->allOrdered();
        }

        return MerchantAssessConfigResource::makeFrom($dimensions, $rules)->toArray();
    }

    /**
     * @param array{
     *   dimensions: array<string, array{weight: int, onboarding_weight: int}>,
     *   rules: list<array{rule_id: string, score: int, config: array<string, mixed>, enabled: bool}>
     * } $data
     * @return array<string, mixed>
     */
    public function saveConfig(array $data): array
    {
        Db::transaction(function () use ($data): void {
            (new MerchantAssessDimensionRepository())->upsertBatch($data['dimensions']);
            (new MerchantAssessRuleRepository())->upsertByRuleId($data['rules']);
        });

        return $this->getConfig();
    }

    private function seedDefaults(): void
    {
        Db::name('merchant_assess_rule')->delete(true);
        Db::name('merchant_assess_dimension')->delete(true);

        Db::name('merchant_assess_dimension')->insertAll(
            (new MerchantAssessDimensionFactory())->builtinRows()
        );
        Db::name('merchant_assess_rule')->insertAll(
            (new MerchantAssessRuleFactory())->builtinRows()
        );
    }
}
