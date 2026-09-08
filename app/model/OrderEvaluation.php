<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 订单风控评估结果（物理表 risk_order_evaluation）
 *
 * @property int         $id
 * @property string      $order_no
 * @property string|null $doopsun_order_id
 * @property string      $merchant_id
 * @property string      $risk_level
 * @property string|null $action
 * @property string|null $measure_code
 * @property string      $decision
 * @property string      $evaluated_at
 * @property string      $created_at
 */
class OrderEvaluation extends Model
{
    protected $name = 'order_evaluation';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = false;

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

    public const DECISION_PASS          = 'pass';
    public const DECISION_DECLINE       = 'decline';
    public const DECISION_CHALLENGE_3DS = 'challenge_3ds';

    /** @var list<string> */
    public const DECISIONS = [
        self::DECISION_PASS,
        self::DECISION_DECLINE,
        self::DECISION_CHALLENGE_3DS,
    ];

    /** @var array<string, string> */
    public const DECISION_LABELS = [
        self::DECISION_PASS          => '通过',
        self::DECISION_DECLINE       => '拒绝',
        self::DECISION_CHALLENGE_3DS => '3DS 挑战',
    ];

    public const STATUS_SUCCESS = 'success';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_FAILED  = 'failed';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_SUCCESS,
        self::STATUS_BLOCKED,
        self::STATUS_FAILED,
    ];

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_SUCCESS => '成功',
        self::STATUS_BLOCKED => '拦截',
        self::STATUS_FAILED  => '失败',
    ];
}
