<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 风控处置策略（物理表 risk_disposition）
 *
 * @property int         $id
 * @property string      $code
 * @property string      $name
 * @property string      $description
 * @property string      $risk_level
 * @property string      $scope
 * @property int         $priority
 * @property int|bool    $is_block
 * @property int|bool    $push_alert
 * @property int|bool    $status
 * @property string      $created_at
 * @property string      $updated_at
 */
class Disposition extends Model
{
    protected $name = 'disposition';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** @var list<string> */
    public const SCOPES = ['transaction', 'merchant'];

    /** @var array<string, string> */
    public const SCOPE_LABELS = [
        'transaction' => '交易级',
        'merchant'    => '商户级',
    ];

    /** @var list<string> */
    public const RISK_LEVELS = ['low', 'mid', 'high', 'critical'];

    /** @var array<string, string> */
    public const RISK_LEVEL_LABELS = [
        'low'      => '低风险',
        'mid'      => '中风险',
        'high'     => '高风险',
        'critical' => '极高风险',
    ];

    /**
     * 列表「今日触发次数」假数据（不入库；后续接真实流水）
     *
     * @var array<string, int>
     */
    public const FAKE_TODAY_TRIGGER_COUNTS = [
        'DECLINE'            => 12,
        'SUSPEND_MERCHANT'   => 3,
        '3DS_CHALLENGE'      => 28,
        'DELAY_SETTLE'       => 5,
        'LIMIT_AMOUNT'       => 2,
        'WATCHLIST'          => 7,
        'CHARGEBACK_INQUIRY' => 4,
        'ALERT_ONLY'         => 31,
    ];
}
