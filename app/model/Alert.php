<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 交易预警（物理表 risk_alert）
 *
 * @property int         $id
 * @property string      $alert_no
 * @property string      $scope
 * @property string      $alerted_at
 * @property string      $merchant_id
 * @property string|null $order_no
 * @property string      $amount_display
 * @property string      $risk_level
 * @property string      $rule_name
 * @property string      $measure_code
 * @property string      $action_name
 * @property array|string|null $hit_details
 * @property string      $status
 * @property string|null $handle_remark
 * @property string|null $inquiry_desc
 * @property int|null    $evaluation_id
 * @property string|null $str_report_id
 * @property int|null    $operator_id
 * @property string|null $handled_at
 * @property string      $created_at
 * @property string      $updated_at
 */
class Alert extends Model
{
    protected $name = 'alert';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** @var array<string, string> */
    protected $type = [
        'evaluation_id' => 'integer',
        'operator_id'   => 'integer',
        'hit_details'   => 'json',
    ];

    public const SCOPE_ORDER = 'order';

    public const RISK_LEVEL_LOW      = 'low';
    public const RISK_LEVEL_MID      = 'mid';
    public const RISK_LEVEL_HIGH     = 'high';
    public const RISK_LEVEL_CRITICAL = 'critical';

    /** @var list<string> */
    public const RISK_LEVELS = [
        self::RISK_LEVEL_LOW,
        self::RISK_LEVEL_MID,
        self::RISK_LEVEL_HIGH,
        self::RISK_LEVEL_CRITICAL,
    ];

    /** @var array<string, string> */
    public const RISK_LEVEL_LABELS = [
        self::RISK_LEVEL_LOW      => '低风险',
        self::RISK_LEVEL_MID      => '中风险',
        self::RISK_LEVEL_HIGH     => '高风险',
        self::RISK_LEVEL_CRITICAL => '极高风险',
    ];

    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_CLOSED     = 'closed';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_CLOSED,
    ];

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_PENDING    => '待处理',
        self::STATUS_PROCESSING => '处理中',
        self::STATUS_CLOSED     => '已关闭',
    ];

    public const MEASURE_DECLINE            = 'DECLINE';
    public const MEASURE_3DS_CHALLENGE      = '3DS_CHALLENGE';
    public const MEASURE_ALERT_ONLY         = 'ALERT_ONLY';
    public const MEASURE_CHARGEBACK_INQUIRY = 'CHARGEBACK_INQUIRY';
    public const MEASURE_DELAY_SETTLE       = 'DELAY_SETTLE';
    public const MEASURE_LIMIT_AMOUNT       = 'LIMIT_AMOUNT';
    public const MEASURE_WATCHLIST          = 'WATCHLIST';

    /** @var list<string> */
    public const MEASURE_CODES = [
        self::MEASURE_DECLINE,
        self::MEASURE_3DS_CHALLENGE,
        self::MEASURE_ALERT_ONLY,
        self::MEASURE_CHARGEBACK_INQUIRY,
        self::MEASURE_DELAY_SETTLE,
        self::MEASURE_LIMIT_AMOUNT,
        self::MEASURE_WATCHLIST,
    ];

    /** @var array<string, string> */
    public const MEASURE_LABELS = [
        self::MEASURE_DECLINE            => '拒绝交易',
        self::MEASURE_3DS_CHALLENGE      => '3DS强验',
        self::MEASURE_ALERT_ONLY         => '仅预警',
        self::MEASURE_CHARGEBACK_INQUIRY => '调单',
        self::MEASURE_DELAY_SETTLE       => '延迟结算',
        self::MEASURE_LIMIT_AMOUNT       => '限制单笔额度',
        self::MEASURE_WATCHLIST          => '加入观察',
    ];

    public const ACTION_CLOSE            = 'close';
    public const ACTION_FALSE_POSITIVE   = 'false_positive';
    public const ACTION_SUBMIT_MATERIALS = 'submit_materials';
    public const ACTION_COMPLETE         = 'complete';

    /** @var list<string> */
    public const HANDLE_ACTIONS = [
        self::ACTION_CLOSE,
        self::ACTION_FALSE_POSITIVE,
        self::ACTION_SUBMIT_MATERIALS,
        self::ACTION_COMPLETE,
    ];

    /** @var array<string, string> */
    public const HANDLE_ACTION_LABELS = [
        self::ACTION_CLOSE            => '关闭预警',
        self::ACTION_FALSE_POSITIVE   => '误报关闭',
        self::ACTION_SUBMIT_MATERIALS => '提交调单材料（维持调单）',
        self::ACTION_COMPLETE         => '调单完成，关闭',
    ];

    public function isInquiry(): bool
    {
        return (string) $this->measure_code === self::MEASURE_CHARGEBACK_INQUIRY;
    }

    public function isClosed(): bool
    {
        return (string) $this->status === self::STATUS_CLOSED;
    }
}
