<?php

declare(strict_types=1);

namespace app\resource;

use app\model\EddCase;
use app\model\MerchantAssessment;

/**
 * EDD 工单列表 / 详情对外形状
 */
class EddCaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $trigger   = (string) $this->trigger;
        $status    = (string) $this->status;
        $riskLevel = (string) $this->risk_level;
        $checklist = $this->checklist;
        if (!is_array($checklist)) {
            $checklist = [];
        }

        $docsReady   = $this->docs_ready;
        $missingKeys = $this->missing_keys;
        $docs        = $this->docs;
        $items       = $this->checklist_items;

        $row = [
            'id'            => (int) $this->id,
            'case_no'       => (string) $this->case_no,
            'merchant_id'   => (string) $this->merchant_id,
            'merchant_name' => (string) $this->merchant_name,
            'trigger'       => $trigger,
            'trigger_label' => EddCase::TRIGGER_LABELS[$trigger] ?? $trigger,
            'risk_level'    => $riskLevel,
            'risk_level_label' => MerchantAssessment::RISK_LEVEL_LABELS[$riskLevel] ?? $riskLevel,
            'status'        => $status,
            'status_label'  => EddCase::STATUS_LABELS[$status] ?? $status,
            'deadline'      => (string) $this->deadline,
            'assignee'      => $this->assignee !== null && $this->assignee !== '' ? (string) $this->assignee : null,
            'progress'      => (int) $this->progress,
            'checklist'     => $checklist,
            'notes'         => $this->notes !== null && $this->notes !== '' ? (string) $this->notes : null,
            'linked_str_id' => $this->linked_str_id !== null && $this->linked_str_id !== ''
                ? (string) $this->linked_str_id
                : null,
            'review_remark' => $this->review_remark !== null && $this->review_remark !== ''
                ? (string) $this->review_remark
                : null,
            'reviewed_at'   => $this->reviewed_at !== null && $this->reviewed_at !== ''
                ? (string) $this->reviewed_at
                : null,
            'created_at'    => (string) $this->created_at,
            'updated_at'    => (string) $this->updated_at,
            'docs_ready'    => (bool) ($docsReady ?? false),
            'missing_keys'  => is_array($missingKeys) ? $missingKeys : [],
        ];

        if (is_array($docs)) {
            $row['docs'] = $docs;
        }

        if (is_array($items)) {
            $row['checklist_items'] = $items;
        }

        return $row;
    }
}
