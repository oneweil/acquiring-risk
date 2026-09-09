<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\StrPushConfig;
use think\db\exception\DbException;

class StrPushConfigRepository
{
    /**
     * @throws DbException
     */
    public function get(): ?StrPushConfig
    {
        return StrPushConfig::find(StrPushConfig::SINGLETON_ID);
    }

    /**
     * @param array{
     *   enabled: bool,
     *   push_by_risk_level: bool,
     *   risk_levels: list<string>,
     *   push_ltr: bool,
     *   ltr_threshold_usd: int,
     *   ltr_threshold_hkd: int
     * } $data
     *
     * @throws DbException
     */
    public function saveOrCreate(array $data): StrPushConfig
    {
        $payload = [
            'enabled'            => !empty($data['enabled']) ? 1 : 0,
            'push_by_risk_level' => !empty($data['push_by_risk_level']) ? 1 : 0,
            'risk_levels'        => array_values($data['risk_levels']),
            'push_ltr'           => !empty($data['push_ltr']) ? 1 : 0,
            'ltr_threshold_usd'  => (int) $data['ltr_threshold_usd'],
            'ltr_threshold_hkd'  => (int) $data['ltr_threshold_hkd'],
        ];

        $model = $this->get();
        if ($model === null) {
            return StrPushConfig::create(array_merge(
                ['id' => StrPushConfig::SINGLETON_ID],
                $payload
            ));
        }

        $model->save($payload);

        return $model;
    }
}
