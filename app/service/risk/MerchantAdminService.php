<?php

declare(strict_types=1);

namespace app\service\risk;

use app\model\Merchant;
use app\repository\risk\MerchantAssessmentRepository;
use app\repository\risk\MerchantLevelConfigRepository;
use app\repository\risk\MerchantRepository;
use app\repository\risk\OrderEvaluationRepository;
use app\resource\MerchantResource;

/**
 * 后台商户列表：本地投影 + 风控评估组装
 */
class MerchantAdminService
{
    public function __construct(
        private readonly MerchantRepository $merchantRepo = new MerchantRepository(),
        private readonly MerchantAssessmentRepository $assessRepo = new MerchantAssessmentRepository(),
        private readonly MerchantLevelConfigRepository $levelConfigRepo = new MerchantLevelConfigRepository(),
        private readonly OrderEvaluationRepository $evalRepo = new OrderEvaluationRepository(),
    ) {
    }

    /**
     * @return array{total: int, active: int, restricted: int, low: int, mid: int, high: int}
     */
    public function stats(): array
    {
        $buckets = $this->merchantRepo->countByStatus();
        $levels  = $this->assessRepo->countByRiskLevel();

        return [
            'total'      => $this->merchantRepo->countAll(),
            'active'     => (int) ($buckets['normal'] ?? 0),
            'restricted' => (int) ($buckets['suspended'] ?? 0) + (int) ($buckets['restricted'] ?? 0),
            'low'        => (int) ($levels['low'] ?? 0),
            'mid'        => (int) ($levels['mid'] ?? 0),
            'high'       => (int) ($levels['high'] ?? 0),
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed> ThinkPHP 分页形状 + Resource data
     */
    public function search(array $filters, int $page, int $pageSize): array
    {
        $localFilters = $filters;
        if (isset($filters['risk_level'])) {
            $ids = $this->assessRepo->merchantIdsByRiskLevel((string) $filters['risk_level']);
            $localFilters['merchant_ids'] = $ids;
            unset($localFilters['risk_level']);
        }

        $paginator = $this->merchantRepo->search($localFilters, $page, $pageSize);
        $models    = [];
        foreach ($paginator as $item) {
            if ($item instanceof Merchant) {
                $models[] = $item;
            }
        }

        $assembled = $this->assembleListRows($models);
        $payload   = $paginator->toArray();
        $payload['data'] = MerchantResource::collection($assembled);

        return $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detail(string $merchantId): ?array
    {
        $model = $this->merchantRepo->findByMerchantId($merchantId);
        if ($model === null) {
            return null;
        }

        $rows = $this->assembleListRows([$model], true);
        $row  = $rows[0] ?? null;
        if ($row === null) {
            return null;
        }

        $row['level_rule_text'] = $this->levelRuleText();

        return MerchantResource::make($row)->toArray();
    }

    /**
     * @param list<Merchant> $models
     * @return list<array<string, mixed>>
     */
    private function assembleListRows(array $models, bool $withDetails = false): array
    {
        $merchantIds = [];
        foreach ($models as $model) {
            $mid = (string) $model->merchant_id;
            if ($mid !== '') {
                $merchantIds[] = $mid;
            }
        }

        $assessMap = $this->assessRepo->mapByMerchantIds($merchantIds);
        $todayMap  = $this->evalRepo->todayAggByMerchantIds($merchantIds);

        $out = [];
        foreach ($models as $model) {
            $mid    = (string) $model->merchant_id;
            $assess = $assessMap[$mid] ?? null;
            $today  = $todayMap[$mid] ?? ['today_count' => 0, 'today_amount' => 0.0];

            $details = [];
            if ($withDetails && $assess !== null) {
                $details = $assess->assess_details;
                if (!is_array($details)) {
                    $details = [];
                }
            }

            $registerAt = $model->register_at ? substr((string) $model->register_at, 0, 10) : null;
            $onboardAt  = $model->onboard_at ? substr((string) $model->onboard_at, 0, 10) : null;

            $out[] = [
                'merchant_id'          => $mid !== '' ? $mid : null,
                'name'                 => $this->nullIfEmpty($model->name),
                'website'              => $this->nullIfEmpty($model->website),
                'onboard_date'         => $onboardAt,
                'email'                => $this->nullIfEmpty($model->email),
                'mobile'               => $this->nullIfEmpty($model->mobile),
                'address'              => $this->nullIfEmpty($model->address),
                'industry'             => $this->nullIfEmpty($model->industry),
                'country'              => $this->nullIfEmpty($model->country),
                'register_date'        => $registerAt,
                'register_days'        => $this->registerDays($registerAt),
                'risk_score'           => $assess !== null ? (int) $assess->risk_score : null,
                'risk_level'           => $assess !== null ? (string) $assess->risk_level : null,
                'chargeback_rate'      => null,
                'fraud_rate'           => null,
                'refund_rate'          => null,
                'volume_anomaly_ratio' => null,
                'assessed_at'          => $assess !== null ? (string) $assess->assessed_at : null,
                'today_count'          => (int) ($today['today_count'] ?? 0),
                'today_amount'         => round((float) ($today['today_amount'] ?? 0), 2),
                'review_status'        => $this->nullIfEmpty($model->review_status),
                'review_time'          => null,
                'review_remark'        => null,
                'trading_status'       => (string) $model->status,
                'website_status'       => $assess !== null
                    ? $assess->website_status
                    : $model->website_status,
                'compliance_hits'      => $assess !== null && $assess->compliance_hits !== null
                    ? (int) $assess->compliance_hits
                    : ($model->compliance_hits !== null ? (int) $model->compliance_hits : null),
                'assess_type'          => $assess !== null ? (string) $assess->assess_type : null,
                'assess_details'       => $details,
                'level_rule_text'      => null,
            ];
        }

        return $out;
    }

    private function registerDays(?string $registerAt): ?int
    {
        if ($registerAt === null || $registerAt === '') {
            return null;
        }
        $ts = strtotime($registerAt);
        if ($ts === false) {
            return null;
        }

        return (int) max(0, floor((time() - $ts) / 86400));
    }

    private function levelRuleText(): string
    {
        $config = $this->levelConfigRepo->get();
        $lowMax = $config !== null ? (int) $config->low_max : 40;
        $midMax = $config !== null ? (int) $config->mid_max : 70;

        return '≤' . $lowMax . ' 低风险；' . ($lowMax + 1) . '–' . $midMax . ' 中风险；>' . $midMax . ' 高风险';
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $str = trim((string) $value);

        return $str === '' ? null : $str;
    }
}
