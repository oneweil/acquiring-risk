<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 商户风险等级权益（物理表 risk_merchant_level_policy，固定三档）
 *
 * @property int    $id
 * @property string $level
 * @property int    $settle_days
 * @property int    $margin_rate
 * @property int    $single_limit
 * @property int    $daily_limit
 * @property string $review_cycle
 * @property string $created_at
 * @property string $updated_at
 */
class MerchantLevelPolicy extends Model
{
    protected $name = 'merchant_level_policy';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** @var list<string> */
    public const LEVELS = ['low', 'mid', 'high'];

    /** @var array<string, string> */
    public const LEVEL_LABELS = [
        'low'  => '低风险',
        'mid'  => '中风险',
        'high' => '高风险',
    ];

    /** @var list<string> */
    public const REVIEW_CYCLES = ['quarterly', 'monthly', 'biweekly', 'weekly'];

    /** @var array<string, string> */
    public const REVIEW_CYCLE_LABELS = [
        'quarterly' => '季度',
        'monthly'   => '月度',
        'biweekly'  => '双周',
        'weekly'    => '每周',
    ];

    /** 入网/存续建议（只读，不入库） */
    /** @var array<string, string> */
    public const LEVEL_ADVICE = [
        'low'  => '标准准入；抽检复核',
        'mid'  => '加强监控；限制部分 MCC 大额',
        'high' => '建议拒绝入网或暂停收单；EDD 复核',
    ];
}
