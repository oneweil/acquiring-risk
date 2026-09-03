<?php

declare(strict_types=1);

namespace app\resource;

use app\model\Blacklist;

/**
 * 后台黑名单列表 / 详情对外形状
 */
class BlacklistResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $type      = (string) $this->type;
        $riskLevel = (string) $this->risk_level;
        $status    = (bool) $this->status;
        $expiry    = $this->expiry_date;
        if ($expiry === '') {
            $expiry = null;
        }

        return [
            'id'               => (int) $this->id,
            'code'             => (string) $this->code,
            'type'             => $type,
            'type_label'       => Blacklist::TYPE_LABELS[$type] ?? $type,
            'value'            => (string) $this->value,
            'reason'           => (string) $this->reason,
            'risk_level'       => $riskLevel,
            'risk_level_label' => Blacklist::RISK_LEVEL_LABELS[$riskLevel] ?? $riskLevel,
            'effective_date'   => (string) $this->effective_date,
            'expiry_date'      => $expiry !== null ? (string) $expiry : null,
            'status'           => $status,
            'status_label'     => $status ? '生效中' : '已失效',
            'created_at'       => (string) $this->created_at,
            'updated_at'       => (string) $this->updated_at,
        ];
    }
}
