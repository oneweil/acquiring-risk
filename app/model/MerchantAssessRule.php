<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 商户评估规则行（物理表 risk_merchant_assess_rule；无 name / measure_code）
 *
 * @property int               $id
 * @property string            $rule_id
 * @property string            $category
 * @property string            $description
 * @property string            $content_template
 * @property int               $score
 * @property array|string|null $config
 * @property int|bool          $enabled
 * @property int               $sort
 * @property string            $created_at
 * @property string            $updated_at
 */
class MerchantAssessRule extends Model
{
    protected $name = 'merchant_assess_rule';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** @var array<string, string> */
    protected $type = [
        'score'   => 'integer',
        'config'  => 'json',
        'enabled' => 'boolean',
        'sort'    => 'integer',
    ];

    /** @var list<string> 卡片/分组顺序 */
    public const CATEGORIES = [
        'industry',
        'chargeback',
        'fraud',
        'tenure',
        'geo',
        'website',
        'compliance',
        'refund',
        'volume_anomaly',
    ];

    /** @var array<string, string> */
    public const CATEGORY_LABELS = [
        'industry'       => '行业风险（Industry / MCC）',
        'chargeback'     => '拒付率（Chargeback Rate · Visa VAMP）',
        'fraud'          => '欺诈率（Fraud Rate · VFMP / EFM）',
        'tenure'         => '注册时长（Registration Tenure）',
        'geo'            => '注册地风险（Geo Risk）',
        'website'        => '网站合规（Website / Domain）',
        'compliance'     => '合规筛查（AML / Sanctions / PEP）',
        'refund'         => '退款率（Refund Rate）',
        'volume_anomaly' => '交易放量异常（Volume Anomaly）',
    ];

    /** 仅已入网复评的分类 */
    /** @var list<string> */
    public const PERIODIC_ONLY_CATEGORIES = [
        'chargeback',
        'fraud',
        'refund',
        'volume_anomaly',
    ];
}
