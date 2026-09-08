<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\MerchantLevelConfig;
use think\db\exception\DbException;

class MerchantLevelConfigRepository
{
    /**
     * @throws DbException
     */
    public function get(): ?MerchantLevelConfig
    {
        return MerchantLevelConfig::find(MerchantLevelConfig::SINGLETON_ID);
    }

    /**
     * @param array{low_max: int, mid_max: int} $data
     *
     * @throws DbException
     */
    public function saveOrCreate(array $data): MerchantLevelConfig
    {
        $model = $this->get();
        if ($model === null) {
            return MerchantLevelConfig::create([
                'id'      => MerchantLevelConfig::SINGLETON_ID,
                'low_max' => $data['low_max'],
                'mid_max' => $data['mid_max'],
            ]);
        }

        $model->save([
            'low_max' => $data['low_max'],
            'mid_max' => $data['mid_max'],
        ]);

        return $model;
    }
}
