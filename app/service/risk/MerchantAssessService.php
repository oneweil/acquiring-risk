<?php

declare(strict_types=1);

namespace app\service\risk;

use app\model\Merchant;
use app\model\MerchantAssessment;
use app\repository\risk\MerchantAssessmentRepository;
use app\repository\risk\MerchantLevelConfigRepository;

/**
 * 商户评估占位引擎（入网型：无交易历史维度）
 */
class MerchantAssessService
{
    /** @var list<string> */
    private const HIGH_RISK_INDUSTRIES = ['虚拟商品', '成人', '博彩', '加密货币'];

    /** @var list<string> */
    private const LOW_RISK_COUNTRIES = ['US', 'GB', 'DE', 'SG', 'HK', 'JP', 'AU', 'CA'];

    public function __construct(
        private readonly MerchantAssessmentRepository $assessRepo = new MerchantAssessmentRepository(),
        private readonly MerchantLevelConfigRepository $levelConfigRepo = new MerchantLevelConfigRepository(),
    ) {
    }

    /**
     * @return MerchantAssessment
     */
    public function assessOnboarding(Merchant $merchant): MerchantAssessment
    {
        $details = [];
        $weightedSum = 0.0;
        $weightSum   = 0;

        $industryScore = $this->scoreIndustry($merchant->industry);
        $details[]     = $this->detailRow('行业风险', 34, $industryScore, '行业 = ' . ($merchant->industry ?: '未知'));
        $weightedSum  += $industryScore * 34;
        $weightSum    += 34;

        $tenureScore = $this->scoreTenure($merchant->register_at);
        $details[]   = $this->detailRow('注册时长', 16, $tenureScore, $this->tenureRuleText($merchant->register_at));
        $weightedSum += $tenureScore * 16;
        $weightSum   += 16;

        $geoScore    = $this->scoreGeo($merchant->country);
        $details[]   = $this->detailRow('注册地风险', 16, $geoScore, '注册国家 = ' . ($merchant->country ?: '未知'));
        $weightedSum += $geoScore * 16;
        $weightSum   += 16;

        $websiteStatus = $merchant->website_status ?: MerchantAssessment::WEBSITE_UNVERIFIED;
        $webScore      = $this->scoreWebsite($websiteStatus);
        $details[]     = $this->detailRow('网站合规', 20, $webScore, '网站状态 = ' . $websiteStatus);
        $weightedSum  += $webScore * 20;
        $weightSum    += 20;

        $hits      = (int) ($merchant->compliance_hits ?? 0);
        $compScore = min(100, $hits * 40);
        $details[] = $this->detailRow('合规筛查', 14, $compScore, '筛查命中 ' . $hits . ' 条');
        $weightedSum += $compScore * 14;
        $weightSum   += 14;

        $score = $weightSum > 0 ? (int) round($weightedSum / $weightSum) : 0;
        $score = max(0, min(100, $score));
        $level = $this->mapLevel($score);

        return $this->assessRepo->upsertByMerchantId([
            'merchant_id'      => (string) $merchant->merchant_id,
            'risk_score'       => $score,
            'risk_level'       => $level,
            'assess_type'      => MerchantAssessment::ASSESS_TYPE_ONBOARDING,
            'website_status'   => $websiteStatus,
            'compliance_hits'  => $hits,
            'assess_details'   => $details,
            'assessed_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    private function mapLevel(int $score): string
    {
        $config = $this->levelConfigRepo->get();
        $lowMax = $config !== null ? (int) $config->low_max : 40;
        $midMax = $config !== null ? (int) $config->mid_max : 70;

        if ($score <= $lowMax) {
            return MerchantAssessment::RISK_LEVEL_LOW;
        }
        if ($score <= $midMax) {
            return MerchantAssessment::RISK_LEVEL_MID;
        }

        return MerchantAssessment::RISK_LEVEL_HIGH;
    }

    private function scoreIndustry(?string $industry): int
    {
        $industry = trim((string) $industry);
        if ($industry === '') {
            return 50;
        }
        foreach (self::HIGH_RISK_INDUSTRIES as $high) {
            if (str_contains($industry, $high)) {
                return 75;
            }
        }

        return 35;
    }

    private function scoreTenure(?string $registerAt): int
    {
        $days = $this->registerDays($registerAt);
        if ($days === null) {
            return 75;
        }
        if ($days < 90) {
            return 75;
        }
        if ($days < 180) {
            return 45;
        }
        if ($days < 365) {
            return 30;
        }

        return 15;
    }

    private function tenureRuleText(?string $registerAt): string
    {
        $days = $this->registerDays($registerAt);
        if ($days === null) {
            return '无注册时间，按新注册档';
        }

        return '注册时长 ' . $days . ' 天';
    }

    private function registerDays(?string $registerAt): ?int
    {
        $registerAt = trim((string) $registerAt);
        if ($registerAt === '') {
            return null;
        }
        $ts = strtotime($registerAt);
        if ($ts === false) {
            return null;
        }

        return (int) max(0, floor((time() - $ts) / 86400));
    }

    private function scoreGeo(?string $country): int
    {
        $country = strtoupper(trim((string) $country));
        if ($country === '') {
            return 50;
        }
        if (in_array($country, self::LOW_RISK_COUNTRIES, true)) {
            return 25;
        }

        return 70;
    }

    private function scoreWebsite(string $status): int
    {
        return match ($status) {
            MerchantAssessment::WEBSITE_COMPLIANT => 15,
            MerchantAssessment::WEBSITE_MISMATCH  => 80,
            default                               => 50,
        };
    }

    /**
     * @return array{name: string, weight: int, raw: int, weighted: float, rule_content: string}
     */
    private function detailRow(string $name, int $weight, int $raw, string $rule): array
    {
        return [
            'name'         => $name,
            'weight'       => $weight,
            'raw'          => $raw,
            'weighted'     => round($raw * $weight / 100, 2),
            'rule_content' => $rule,
        ];
    }
}
