<?php

declare(strict_types=1);

namespace app\resource;

use app\model\OrderEvaluation;

/**
 * 订单监控列表 / 详情对外形状（组装后的 array）
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        /** @var array<string, mixed> $row */
        $row = is_array($this->resource) ? $this->resource : [];

        $riskLevel = $row['risk_level'] ?? null;
        $status    = $row['status'] ?? null;
        $decision  = $row['decision'] ?? null;

        return [
            'order_no'         => $row['order_no'] ?? null,
            'channel_no'       => $row['channel_no'] ?? null,
            'merchant_id'      => $row['merchant_id'] ?? null,
            'merchant_name'    => $row['merchant_name'] ?? null,
            'trade_time'       => $row['trade_time'] ?? null,
            'currency'         => $row['currency'] ?? null,
            'amount'           => $row['amount'] ?? null,
            'card_type'        => $row['card_type'] ?? null,
            'card_country'     => $row['card_country'] ?? null,
            'ip_country'       => $row['ip_country'] ?? null,
            'three_ds'         => $row['three_ds'] ?? null,
            'status'           => $status,
            'status_label'     => $status === null
                ? null
                : (OrderEvaluation::STATUS_LABELS[$status] ?? (string) $status),
            'risk_level'       => $riskLevel,
            'risk_level_label' => $riskLevel === null
                ? null
                : (OrderEvaluation::RISK_LEVEL_LABELS[$riskLevel] ?? (string) $riskLevel),
            'hit_rule'         => $row['hit_rule'] ?? '',
            'action'           => $row['action'] ?? null,
            'decision'         => $decision,
            'decision_label'   => $decision === null
                ? null
                : (OrderEvaluation::DECISION_LABELS[$decision] ?? (string) $decision),
            'measure_code'     => $row['measure_code'] ?? null,
            'evaluated_at'     => $row['evaluated_at'] ?? null,
            'website'          => $row['website'] ?? null,
            'card_no'          => $row['card_no'] ?? null,
            'card_bin'         => $row['card_bin'] ?? null,
            'avs_result'       => $row['avs_result'] ?? null,
            'cvv_result'       => $row['cvv_result'] ?? null,
            'eci'              => $row['eci'] ?? null,
            'email'            => $row['email'] ?? null,
            'ip'               => $row['ip'] ?? null,
            'billing_country'  => $row['billing_country'] ?? null,
            'shipping_country' => $row['shipping_country'] ?? null,
            'mcc'              => $row['mcc'] ?? null,
            'is_proxy'         => (bool) ($row['is_proxy'] ?? false),
            'hit_details'      => is_array($row['hit_details'] ?? null) ? $row['hit_details'] : [],
        ];
    }
}
