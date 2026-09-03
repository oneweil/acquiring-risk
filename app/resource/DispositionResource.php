<?php

declare(strict_types=1);

namespace app\resource;

use app\model\Disposition;

/**
 * 后台处置策略列表 / 详情对外形状
 */
class DispositionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $code      = (string) $this->code;
        $riskLevel = (string) $this->risk_level;
        $scope     = (string) $this->scope;
        $status    = (bool) $this->status;
        $isBlock   = (bool) $this->is_block;
        $pushAlert = (bool) $this->push_alert;

        return [
            'id'                  => (int) $this->id,
            'code'                => $code,
            'name'                => (string) $this->name,
            'description'         => (string) $this->description,
            'risk_level'          => $riskLevel,
            'risk_level_label'    => Disposition::RISK_LEVEL_LABELS[$riskLevel] ?? $riskLevel,
            'scope'               => $scope,
            'scope_label'         => Disposition::SCOPE_LABELS[$scope] ?? $scope,
            'priority'            => (int) $this->priority,
            'is_block'            => $isBlock,
            'is_block_label'      => $isBlock ? '是' : '否',
            'push_alert'          => $pushAlert,
            'push_alert_label'    => $pushAlert ? '是' : '否',
            'status'              => $status,
            'status_label'        => $status ? '启用' : '停用',
            'today_trigger_count' => Disposition::FAKE_TODAY_TRIGGER_COUNTS[$code] ?? 0,
            'created_at'          => (string) $this->created_at,
            'updated_at'          => (string) $this->updated_at,
        ];
    }
}
