<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 商户风险等级分值映射（物理表 risk_merchant_level_config，单行）
 *
 * @property int    $id
 * @property int    $low_max
 * @property int    $mid_max
 * @property string $created_at
 * @property string $updated_at
 */
class MerchantLevelConfig extends Model
{
    protected $name = 'merchant_level_config';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** 固定配置行主键 */
    public const SINGLETON_ID = 1;
}
