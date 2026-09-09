<?php

declare(strict_types=1);

namespace app\resource;

use app\model\Alert;

/**
 * 交易预警列表 / 详情对外形状
 */
class AlertResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $riskLevel   = (string) $this->risk_level;
        $status      = (string) $this->status;
        $measureCode = (string) $this->measure_code;
        $hitDetails  = $this->hit_details;
        if (!is_array($hitDetails)) {
            $hitDetails = [];
        }

        $attachments = $this->attachments;
        $strSummary  = $this->str_summary;
        $attachmentCount = $this->attachment_count;

        $row = [
            'id'               => (int) $this->id,
            'alert_no'         => (string) $this->alert_no,
            'scope'            => (string) $this->scope,
            'alerted_at'       => (string) $this->alerted_at,
            'merchant_id'      => (string) $this->merchant_id,
            'order_no'         => $this->order_no !== null && $this->order_no !== ''
                ? (string) $this->order_no
                : null,
            'amount_display'   => (string) $this->amount_display,
            'risk_level'       => $riskLevel,
            'risk_level_label' => Alert::RISK_LEVEL_LABELS[$riskLevel] ?? $riskLevel,
            'rule_name'        => (string) $this->rule_name,
            'measure_code'     => $measureCode,
            'measure_label'    => Alert::MEASURE_LABELS[$measureCode]
                ?? ((string) $this->action_name !== '' ? (string) $this->action_name : $measureCode),
            'action_name'      => (string) $this->action_name,
            'status'           => $status,
            'status_label'     => Alert::STATUS_LABELS[$status] ?? $status,
            'handle_remark'    => $this->handle_remark !== null && $this->handle_remark !== ''
                ? (string) $this->handle_remark
                : null,
            'inquiry_desc'     => $this->inquiry_desc !== null && $this->inquiry_desc !== ''
                ? (string) $this->inquiry_desc
                : null,
            'str_report_id'    => $this->str_report_id !== null && $this->str_report_id !== ''
                ? (string) $this->str_report_id
                : null,
            'is_inquiry'       => $measureCode === Alert::MEASURE_CHARGEBACK_INQUIRY,
            'can_handle'       => $status !== Alert::STATUS_CLOSED,
            'hit_details'      => $hitDetails,
            'attachment_count' => is_numeric($attachmentCount) ? (int) $attachmentCount : 0,
            'operator_id'      => $this->operator_id !== null ? (int) $this->operator_id : null,
            'handled_at'       => $this->handled_at !== null && $this->handled_at !== ''
                ? (string) $this->handled_at
                : null,
            'created_at'       => (string) $this->created_at,
            'updated_at'       => (string) $this->updated_at,
        ];

        if (is_array($attachments)) {
            $row['attachments'] = $attachments;
        }

        if (is_array($strSummary)) {
            $row['str_summary'] = $strSummary;
        } elseif ($row['str_report_id'] !== null) {
            $row['str_summary'] = [
                'report_no' => $row['str_report_id'],
                'status'    => null,
                'status_label' => null,
            ];
        } else {
            $row['str_summary'] = null;
        }

        return $row;
    }
}
