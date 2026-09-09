<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * STR/LTR 自动推送全局配置（物理表 risk_str_push_config，单行）
 *
 * @property int         $id
 * @property int|bool    $enabled
 * @property int|bool    $push_by_risk_level
 * @property array|string|null $risk_levels
 * @property int|bool    $push_ltr
 * @property int         $ltr_threshold_usd
 * @property int         $ltr_threshold_hkd
 * @property string      $created_at
 * @property string      $updated_at
 */
class StrPushConfig extends Model
{
    protected $name = 'str_push_config';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** 固定配置行主键 */
    public const SINGLETON_ID = 1;

    public const DEFAULT_LTR_USD = 10000;

    public const DEFAULT_LTR_HKD = 50000;

    /** @var list<string> */
    public const DEFAULT_RISK_LEVELS = ['high', 'critical'];

    /** @var list<string> 默认对下列规则类别启用 push_str */
    public const DEFAULT_PUSH_CATEGORIES = [
        'fraud',
        'geo_sanctions',
        'blacklist',
        'merchant',
    ];

    /** @var array<string, string> */
    protected $type = [
        'enabled'            => 'boolean',
        'push_by_risk_level' => 'boolean',
        'risk_levels'        => 'json',
        'push_ltr'           => 'boolean',
        'ltr_threshold_usd'  => 'integer',
        'ltr_threshold_hkd'  => 'integer',
    ];
}
