<?php

declare(strict_types=1);

namespace app\resource;

use app\model\MerchantAssessment;

/**
 * 商户列表 / 详情对外形状（组装后的 array）
 */
class MerchantResource extends JsonResource
{
    public const TRADING_STATUS_LABELS = [
        'normal'     => '正常',
        'watch'      => '观察',
        'restricted' => '受限',
        'suspended'  => '暂停',
        'not_opened' => '未开通',
    ];

    public const REVIEW_STATUS_LABELS = [
        'approved' => '通过',
        'rejected' => '不通过',
    ];

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        /** @var array<string, mixed> $row */
        $row = is_array($this->resource) ? $this->resource : [];

        $riskLevel    = $row['risk_level'] ?? null;
        $reviewStatus = $row['review_status'] ?? null;
        $tradingStatus = $row['trading_status'] ?? null;
        $websiteStatus = $row['website_status'] ?? null;
        $assessType    = $row['assess_type'] ?? null;

        $rejected = $reviewStatus === 'rejected';

        return [
            'merchant_id'             => $row['merchant_id'] ?? null,
            'name'                    => $row['name'] ?? null,
            'website'                 => $row['website'] ?? null,
            'onboard_date'            => $row['onboard_date'] ?? null,
            'email'                   => $row['email'] ?? null,
            'mobile'                  => $row['mobile'] ?? null,
            'address'                 => $row['address'] ?? null,
            'industry'                => $row['industry'] ?? null,
            'country'                 => $row['country'] ?? null,
            'register_date'           => $row['register_date'] ?? null,
            'register_days'           => $row['register_days'] ?? null,
            'risk_score'              => $rejected ? null : ($row['risk_score'] ?? null),
            'risk_level'              => $rejected ? null : $riskLevel,
            'risk_level_label'        => $rejected || $riskLevel === null
                ? null
                : (MerchantAssessment::RISK_LEVEL_LABELS[$riskLevel] ?? (string) $riskLevel),
            'chargeback_rate'         => $rejected ? null : ($row['chargeback_rate'] ?? null),
            'fraud_rate'              => $rejected ? null : ($row['fraud_rate'] ?? null),
            'refund_rate'             => $row['refund_rate'] ?? null,
            'volume_anomaly_ratio'    => $row['volume_anomaly_ratio'] ?? null,
            'assessed_at'             => $row['assessed_at'] ?? null,
            'today_count'             => $rejected ? null : ($row['today_count'] ?? null),
            'today_amount'            => $rejected ? null : ($row['today_amount'] ?? null),
            'review_status'           => $reviewStatus,
            'review_status_label'     => $reviewStatus === null
                ? null
                : (self::REVIEW_STATUS_LABELS[$reviewStatus] ?? (string) $reviewStatus),
            'review_time'             => $row['review_time'] ?? null,
            'review_remark'           => $row['review_remark'] ?? null,
            'trading_status'          => $tradingStatus,
            'trading_status_label'    => $tradingStatus === null
                ? null
                : (self::TRADING_STATUS_LABELS[$tradingStatus] ?? (string) $tradingStatus),
            'website_status'          => $websiteStatus,
            'website_status_label'    => $websiteStatus === null
                ? null
                : (MerchantAssessment::WEBSITE_STATUS_LABELS[$websiteStatus] ?? (string) $websiteStatus),
            'compliance_hits'         => $row['compliance_hits'] ?? null,
            'assess_type'             => $assessType,
            'assess_type_label'       => $assessType === null
                ? null
                : (MerchantAssessment::ASSESS_TYPE_LABELS[$assessType] ?? (string) $assessType),
            'assess_details'          => $row['assess_details'] ?? [],
            'level_rule_text'         => $row['level_rule_text'] ?? null,
        ];
    }
}
