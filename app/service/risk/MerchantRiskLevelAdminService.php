<?php

declare(strict_types=1);

namespace app\service\risk;

use app\repository\risk\MerchantLevelConfigRepository;
use app\repository\risk\MerchantLevelPolicyRepository;
use app\resource\MerchantLevelConfigResource;
use database\factories\MerchantLevelConfigFactory;
use think\facade\Db;

/**
 * 后台商户风险等级规则（分值映射 + 三档权益）
 */
class MerchantRiskLevelAdminService
{
    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $configRepo = new MerchantLevelConfigRepository();
        $policyRepo = new MerchantLevelPolicyRepository();

        $config = $configRepo->get();
        if ($config === null) {
            $factory = new MerchantLevelConfigFactory();
            $builtin = $factory->builtinConfig();
            $config  = $configRepo->saveOrCreate([
                'low_max' => (int) $builtin['low_max'],
                'mid_max' => (int) $builtin['mid_max'],
            ]);
            $policyRepo->upsertBatch($this->policiesFromFactory($factory));
        }

        $policies = $policyRepo->allByLevel();
        if (count($policies) < count(\app\model\MerchantLevelPolicy::LEVELS)) {
            $factory = new MerchantLevelConfigFactory();
            $policyRepo->upsertBatch($this->policiesFromFactory($factory));
            $policies = $policyRepo->allByLevel();
        }

        return MerchantLevelConfigResource::makeWithPolicies($config, $policies)->toArray();
    }

    /**
     * @param array{config: array{low_max: int, mid_max: int}, policies: array<string, array{settle_days: int, margin_rate: int, single_limit: int, daily_limit: int, review_cycle: string}>} $data
     * @return array<string, mixed>
     */
    public function saveConfig(array $data): array
    {
        Db::transaction(function () use ($data): void {
            (new MerchantLevelConfigRepository())->saveOrCreate($data['config']);
            (new MerchantLevelPolicyRepository())->upsertBatch($data['policies']);
        });

        return $this->getConfig();
    }

    /**
     * @return array<string, array{settle_days: int, margin_rate: int, single_limit: int, daily_limit: int, review_cycle: string}>
     */
    private function policiesFromFactory(MerchantLevelConfigFactory $factory): array
    {
        $map = [];
        foreach ($factory->builtinPolicies() as $row) {
            $level = (string) $row['level'];
            $map[$level] = [
                'settle_days'  => (int) $row['settle_days'],
                'margin_rate'  => (int) $row['margin_rate'],
                'single_limit' => (int) $row['single_limit'],
                'daily_limit'  => (int) $row['daily_limit'],
                'review_cycle' => (string) $row['review_cycle'],
            ];
        }

        return $map;
    }
}
