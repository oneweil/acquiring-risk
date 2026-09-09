<?php

declare(strict_types=1);

namespace app\resource;

use app\model\Disposition;

/**
 * STR 推送全局配置对外形状
 */
class StrPushConfigResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $levels = $this->risk_levels;
        if (is_string($levels)) {
            $decoded = json_decode($levels, true);
            $levels  = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($levels)) {
            $levels = [];
        }

        $levelLabels = [];
        foreach ($levels as $level) {
            $level = (string) $level;
            $levelLabels[] = Disposition::RISK_LEVEL_LABELS[$level] ?? $level;
        }

        return [
            'enabled'            => (bool) $this->enabled,
            'push_by_risk_level' => (bool) $this->push_by_risk_level,
            'risk_levels'        => array_values(array_map('strval', $levels)),
            'risk_level_labels'  => $levelLabels,
            'push_ltr'           => (bool) $this->push_ltr,
            'ltr_threshold_usd'  => (int) $this->ltr_threshold_usd,
            'ltr_threshold_hkd'  => (int) $this->ltr_threshold_hkd,
        ];
    }

    /**
     * @param array{enabled_count?: int, total_count?: int} $extra
     * @return array<string, mixed>
     */
    public static function makeWithCounts(mixed $resource, array $extra = []): array
    {
        $data = (new static($resource))->toArray();
        $data['enabled_count'] = (int) ($extra['enabled_count'] ?? 0);
        $data['total_count']   = (int) ($extra['total_count'] ?? 0);

        return $data;
    }
}
