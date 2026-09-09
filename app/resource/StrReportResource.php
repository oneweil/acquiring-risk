<?php

declare(strict_types=1);

namespace app\resource;

use app\model\StrReport;

/**
 * STR/LTR 报送列表 / 详情对外形状
 */
class StrReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $type         = (string) $this->type;
        $status       = (string) $this->status;
        $triggerMode  = (string) ($this->trigger_mode ?: StrReport::TRIGGER_MANUAL);
        $hitRuleIds   = $this->hit_rule_ids;
        if (is_string($hitRuleIds) && $hitRuleIds !== '') {
            $decoded = json_decode($hitRuleIds, true);
            $hitRuleIds = is_array($decoded) ? $decoded : [];
        } elseif (!is_array($hitRuleIds)) {
            $hitRuleIds = [];
        }

        $attachments = $this->attachments;
        $linkedEdd   = $this->linked_edd;
        $attachCount = $this->attachment_count;

        $row = [
            'id'               => (int) $this->id,
            'report_no'        => (string) $this->report_no,
            'type'             => $type,
            'type_label'       => StrReport::TYPE_LABELS[$type] ?? $type,
            'trigger_mode'     => $triggerMode,
            'trigger_mode_label' => StrReport::TRIGGER_MODE_LABELS[$triggerMode] ?? $triggerMode,
            'merchant_id'      => (string) $this->merchant_id,
            'merchant_name'    => (string) $this->merchant_name,
            'order_no'         => (string) $this->order_no,
            'currency'         => (string) $this->currency,
            'amount_val'       => (float) $this->amount_val,
            'amount_display'   => (string) $this->amount_display,
            'trigger_reason'   => (string) $this->trigger_reason,
            'suspicious_desc'  => $this->suspicious_desc !== null && $this->suspicious_desc !== ''
                ? (string) $this->suspicious_desc
                : null,
            'status'           => $status,
            'status_label'     => StrReport::STATUS_LABELS[$status] ?? $status,
            'submitter'        => $this->submitter !== null && $this->submitter !== ''
                ? (string) $this->submitter
                : null,
            'reviewer'         => $this->reviewer !== null && $this->reviewer !== ''
                ? (string) $this->reviewer
                : null,
            'review_remark'    => $this->review_remark !== null && $this->review_remark !== ''
                ? (string) $this->review_remark
                : null,
            'dismiss_reason'   => $this->dismiss_reason !== null && $this->dismiss_reason !== ''
                ? (string) $this->dismiss_reason
                : null,
            'reject_reason'    => $this->reject_reason !== null && $this->reject_reason !== ''
                ? (string) $this->reject_reason
                : null,
            'linked_alert_id'  => $this->linked_alert_id !== null && $this->linked_alert_id !== ''
                ? (string) $this->linked_alert_id
                : null,
            'hit_rule_ids'     => $hitRuleIds,
            'reviewed_at'      => $this->reviewed_at !== null && $this->reviewed_at !== ''
                ? (string) $this->reviewed_at
                : null,
            'submitted_at'     => $this->submitted_at !== null && $this->submitted_at !== ''
                ? (string) $this->submitted_at
                : null,
            'created_at'       => (string) $this->created_at,
            'updated_at'       => (string) $this->updated_at,
            'attachment_count' => (int) ($attachCount ?? 0),
        ];

        if (is_array($attachments)) {
            $row['attachments'] = $attachments;
        }

        if (is_array($linkedEdd) || $linkedEdd === null) {
            $row['linked_edd'] = $linkedEdd;
        }

        return $row;
    }
}
