<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 订单规则命中明细（物理表 risk_order_hit）
 *
 * @property int         $id
 * @property int         $evaluation_id
 * @property string      $rule_id
 * @property string      $rule_name
 * @property string|null $risk_level
 * @property string|null $measure
 * @property string      $created_at
 */
class OrderHit extends Model
{
    protected $name = 'order_hit';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = false;

    /** @var array<string, string> */
    protected $type = [
        'evaluation_id' => 'integer',
    ];
}
