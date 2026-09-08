<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 商户评估结果（物理表 risk_merchant_assessment；每商户最新一条）
 *
 * @property int               $id
 * @property string            $merchant_id
 * @property int               $risk_score
 * @property string            $risk_level
 * @property string            $assess_type
 * @property string|null       $website_status
 * @property int|null          $compliance_hits
 * @property array|string|null $assess_details
 * @property string            $assessed_at
 * @property string            $created_at
 * @property string            $updated_at
 */
class MerchantAssessment extends Model
{
    protected $name = 'merchant_assessment';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** @var array<string, string> */
    protected $type = [
        'risk_score'      => 'integer',
        'compliance_hits' => 'integer',
        'assess_details'  => 'json',
    ];

    public const RISK_LEVEL_LOW  = 'low';
    public const RISK_LEVEL_MID  = 'mid';
    public const RISK_LEVEL_HIGH = 'high';

    /** @var list<string> */
    public const RISK_LEVELS = [
        self::RISK_LEVEL_LOW,
        self::RISK_LEVEL_MID,
        self::RISK_LEVEL_HIGH,
    ];

    /** @var array<string, string> */
    public const RISK_LEVEL_LABELS = [
        self::RISK_LEVEL_LOW  => '低风险',
        self::RISK_LEVEL_MID  => '中风险',
        self::RISK_LEVEL_HIGH => '高风险',
    ];

    public const ASSESS_TYPE_ONBOARDING = 'onboarding';
    public const ASSESS_TYPE_PERIODIC   = 'periodic';

    /** @var array<string, string> */
    public const ASSESS_TYPE_LABELS = [
        self::ASSESS_TYPE_ONBOARDING => '入网评估',
        self::ASSESS_TYPE_PERIODIC   => '存续复评',
    ];

    public const WEBSITE_COMPLIANT   = 'compliant';
    public const WEBSITE_MISMATCH    = 'mismatch';
    public const WEBSITE_UNVERIFIED  = 'unverified';

    /** @var array<string, string> */
    public const WEBSITE_STATUS_LABELS = [
        self::WEBSITE_COMPLIANT  => '网站能访问',
        self::WEBSITE_MISMATCH   => '网站不能访问',
        self::WEBSITE_UNVERIFIED => '未核验',
    ];
}
