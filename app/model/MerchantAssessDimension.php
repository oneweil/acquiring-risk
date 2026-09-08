<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 商户评估维度权重（物理表 risk_merchant_assess_dimension，固定 9 行）
 *
 * @property int    $id
 * @property string $dim_key
 * @property string $name
 * @property int    $weight
 * @property int    $onboarding_weight
 * @property int    $sort
 * @property string $created_at
 * @property string $updated_at
 */
class MerchantAssessDimension extends Model
{
    protected $name = 'merchant_assess_dimension';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** @var array<string, string> */
    protected $type = [
        'weight'            => 'integer',
        'onboarding_weight' => 'integer',
        'sort'              => 'integer',
    ];

    /** @var list<string> */
    public const DIM_KEYS = [
        'industry',
        'chargeback',
        'fraud',
        'tenure',
        'geo',
        'website',
        'compliance',
        'refund',
        'volumeAnomaly',
    ];

    /** @var array<string, string> */
    public const DIM_LABELS = [
        'industry'      => '行业风险',
        'chargeback'    => '拒付率',
        'fraud'         => '欺诈率',
        'tenure'        => '经营时长',
        'geo'           => '注册地风险',
        'website'       => '网站合规',
        'compliance'    => '合规筛查',
        'refund'        => '退款率',
        'volumeAnomaly' => '交易放量',
    ];

    /** 入网评估排除维度（onboarding_weight 须为 0） */
    /** @var list<string> */
    public const ONBOARDING_EXCLUDED_DIMS = [
        'chargeback',
        'fraud',
        'refund',
        'volumeAnomaly',
    ];

    /**
     * 规则 category → 维度 dim_key
     *
     * @var array<string, string>
     */
    public const CATEGORY_TO_DIM = [
        'industry'        => 'industry',
        'chargeback'      => 'chargeback',
        'fraud'           => 'fraud',
        'refund'          => 'refund',
        'website'         => 'website',
        'compliance'      => 'compliance',
        'tenure'          => 'tenure',
        'volume_anomaly'  => 'volumeAnomaly',
        'geo'             => 'geo',
    ];
}
