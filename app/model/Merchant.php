<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 商户投影（物理表 risk_merchant；主站推送）
 *
 * @property int               $id
 * @property string            $merchant_id
 * @property string            $name
 * @property string            $status
 * @property string|null       $industry
 * @property string|null       $country
 * @property string|null       $register_at
 * @property string|null       $onboard_at
 * @property string|null       $website
 * @property string|null       $email
 * @property string|null       $mobile
 * @property string|null       $address
 * @property string|null       $website_status
 * @property int|null          $compliance_hits
 * @property string|null       $review_status
 * @property int               $source_version
 * @property array|string|null $extra
 * @property string            $synced_at
 * @property string            $created_at
 * @property string            $updated_at
 */
class Merchant extends Model
{
    protected $name = 'merchant';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** @var array<string, string> */
    protected $type = [
        'compliance_hits' => 'integer',
        'source_version'  => 'integer',
        'extra'           => 'json',
    ];

    public const STATUS_NORMAL     = 'normal';
    public const STATUS_WATCH      = 'watch';
    public const STATUS_RESTRICTED = 'restricted';
    public const STATUS_SUSPENDED  = 'suspended';
    public const STATUS_NOT_OPENED = 'not_opened';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_NORMAL,
        self::STATUS_WATCH,
        self::STATUS_RESTRICTED,
        self::STATUS_SUSPENDED,
        self::STATUS_NOT_OPENED,
    ];

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_NORMAL     => '正常',
        self::STATUS_WATCH      => '观察',
        self::STATUS_RESTRICTED => '受限',
        self::STATUS_SUSPENDED  => '暂停',
        self::STATUS_NOT_OPENED => '未开通',
    ];

    public const REVIEW_APPROVED = 'approved';
    public const REVIEW_REJECTED = 'rejected';

    /** @var list<string> */
    public const REVIEW_STATUSES = [
        self::REVIEW_APPROVED,
        self::REVIEW_REJECTED,
    ];
}
