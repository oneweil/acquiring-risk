<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 风控规则（物理表 risk_rule）
 *
 * @property int         $id
 * @property string      $rule_id
 * @property string      $category
 * @property string      $name
 * @property string      $description
 * @property string      $content_template
 * @property array|string|null $config
 * @property string      $measure_code
 * @property int|bool    $enabled
 * @property int         $sort
 * @property string      $created_at
 * @property string      $updated_at
 */
class Rule extends Model
{
    protected $name = 'rule';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** @var array<string, string> */
    protected $type = [
        'config'  => 'json',
        'enabled' => 'boolean',
        'sort'    => 'integer',
    ];

    /** @var list<string> 展示顺序 */
    public const CATEGORIES = [
        'card_velocity',
        'ip_velocity',
        'time_pattern',
        'avs_cvv_3ds',
        'amount',
        'geo_sanctions',
        'fraud',
        'merchant',
        'blacklist',
    ];

    /** @var array<string, string> */
    public const CATEGORY_LABELS = [
        'card_velocity' => '卡号频率控制（Card Velocity）',
        'ip_velocity'   => 'IP / 身份频率（Identity Velocity）',
        'time_pattern'  => '交易时间点（Time Pattern）',
        'avs_cvv_3ds'   => 'AVS / CVV / 3DS 验证（Authentication）',
        'amount'        => '交易金额（Amount Limits）',
        'geo_sanctions' => '地理与制裁合规（Geo & Sanctions）',
        'fraud'         => '欺诈行为识别（Fraud Pattern）',
        'merchant'      => '商户监控（Merchant Monitoring）',
        'blacklist'     => '黑名单与 negative file',
    ];
}
