<?php

declare(strict_types=1);

namespace app\service\risk;

use app\repository\doopsun\DoopsunMerchantRepository;
use app\repository\risk\MerchantAssessmentRepository;
use app\repository\risk\MerchantLevelConfigRepository;
use app\resource\MerchantResource;

/**
 * 后台商户列表：doopsun 主数据 + 风控评估应用层组装
 */
class MerchantAdminService
{
    public function __construct(
        private readonly DoopsunMerchantRepository $doopsunRepo = new DoopsunMerchantRepository(),
        private readonly MerchantAssessmentRepository $assessRepo = new MerchantAssessmentRepository(),
        private readonly MerchantLevelConfigRepository $levelConfigRepo = new MerchantLevelConfigRepository(),
    ) {
    }

    /**
     * @return array{total: int, active: int, restricted: int, low: int, mid: int, high: int}
     */
    public function stats(): array
    {
        $buckets = $this->doopsunRepo->countByTradingBucket();
        $levels  = $this->assessRepo->countByRiskLevel();

        return [
            'total'      => $this->doopsunRepo->countAll(),
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
        $doopsunFilters = $filters;
        if (isset($filters['risk_level'])) {
            $ids = $this->assessRepo->merchantIdsByRiskLevel((string) $filters['risk_level']);
            $doopsunFilters['merchant_ids'] = $ids;
            unset($doopsunFilters['risk_level']);
        }

        $paginator = $this->doopsunRepo->search($doopsunFilters, $page, $pageSize);
        $rawRows   = [];
        foreach ($paginator as $item) {
            $rawRows[] = is_array($item) ? $item : (array) $item;
        }

        $assembled = $this->assembleListRows($rawRows);
        $payload   = $paginator->toArray();
        $payload['data'] = MerchantResource::collection($assembled);

        return $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detail(string $merchantId): ?array
    {
        $raw = $this->doopsunRepo->findByMerchantId($merchantId);
        if ($raw === null) {
            return null;
        }

        $rows = $this->assembleListRows([$raw], true);
        $row  = $rows[0] ?? null;
        if ($row === null) {
            return null;
        }

        $row['level_rule_text'] = $this->levelRuleText();

        return MerchantResource::make($row)->toArray();
    }

    /**
     * @param list<array<string, mixed>> $rawRows
     * @return list<array<string, mixed>>
     */
    private function assembleListRows(array $rawRows, bool $withDetails = false): array
    {
        $merchantIds = [];
        foreach ($rawRows as $raw) {
            $mid = (string) ($raw['merchantId'] ?? '');
            if ($mid !== '') {
                $merchantIds[] = $mid;
            }
        }

        $assessMap = $this->assessRepo->mapByMerchantIds($merchantIds);
        $todayMap  = $this->doopsunRepo->todayOrderAggByMerchantIds($merchantIds);

        $out = [];
        foreach ($rawRows as $raw) {
            $mid    = (string) ($raw['merchantId'] ?? '');
            $assess = $assessMap[$mid] ?? null;
            $today  = $todayMap[$mid] ?? ['today_count' => 0, 'today_amount' => 0.0];

            $reviewStatus  = $this->mapReviewStatus($raw);
            $tradingStatus = $this->mapTradingStatus($raw);

            $details = [];
            if ($withDetails && $assess !== null) {
                $details = $assess->assess_details;
                if (!is_array($details)) {
                    $details = [];
                }
            }

            $out[] = [
                'merchant_id'          => $mid !== '' ? $mid : null,
                'name'                 => $this->nullIfEmpty($raw['name'] ?? null),
                'website'              => $this->nullIfEmpty($raw['transaction_url'] ?? null),
                'onboard_date'         => $this->formatOnboardDate($raw),
                'email'                => $this->nullIfEmpty($raw['email'] ?? null),
                'mobile'               => $this->nullIfEmpty($raw['mobile'] ?? null),
                'address'              => $this->nullIfEmpty($raw['address'] ?? null),
                'industry'             => null,
                'country'              => null,
                'register_date'        => null,
                'register_days'        => null,
                'risk_score'           => $assess !== null ? (int) $assess->risk_score : null,
                'risk_level'           => $assess !== null ? (string) $assess->risk_level : null,
                'chargeback_rate'      => null,
                'fraud_rate'           => null,
                'refund_rate'          => null,
                'volume_anomaly_ratio' => null,
                'assessed_at'          => $assess !== null ? (string) $assess->assessed_at : null,
                'today_count'          => (int) ($today['today_count'] ?? 0),
                'today_amount'         => round((float) ($today['today_amount'] ?? 0), 2),
                'review_status'        => $reviewStatus,
                'review_time'          => null,
                'review_remark'        => null,
                'trading_status'       => $tradingStatus,
                'website_status'       => $assess !== null ? $assess->website_status : null,
                'compliance_hits'      => $assess !== null && $assess->compliance_hits !== null
                    ? (int) $assess->compliance_hits
                    : null,
                'assess_type'          => $assess !== null ? (string) $assess->assess_type : null,
                'assess_details'       => $details,
                'level_rule_text'      => null,
            ];
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $raw
     */
    private function mapReviewStatus(array $raw): ?string
    {
        $status = (int) ($raw['status'] ?? -1);
        if ($status === 0) {
            return 'approved';
        }
        if ($status === 2) {
            return 'rejected';
        }

        return null;
    }

    /**
     * @param array<string, mixed> $raw
     */
    private function mapTradingStatus(array $raw): string
    {
        $isAbate   = (int) ($raw['is_abate'] ?? 1);
        $status    = (int) ($raw['status'] ?? 1);
        $isWarning = (int) ($raw['is_warning'] ?? 0);

        if ($isAbate === 0 || $status === 2) {
            return 'suspended';
        }
        if ($status === 1) {
            return 'not_opened';
        }
        if ($status === 0 && $isWarning === 1) {
            return 'watch';
        }
        if ($status === 0) {
            return 'normal';
        }

        return 'not_opened';
    }

    /**
     * @param array<string, mixed> $raw
     */
    private function formatOnboardDate(array $raw): ?string
    {
        $signtime = trim((string) ($raw['signtime'] ?? ''));
        if ($signtime !== '' && $signtime !== '0') {
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $signtime)) {
                return substr($signtime, 0, 10);
            }
            if (ctype_digit($signtime)) {
                $ts = (int) $signtime;

                return $ts > 0 ? date('Y-m-d', $ts) : null;
            }

            return $signtime;
        }

        $inTime = (int) ($raw['inTime'] ?? 0);
        if ($inTime > 0) {
            return date('Y-m-d', $inTime);
        }

        return null;
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
